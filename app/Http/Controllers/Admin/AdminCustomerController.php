<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\CustomerCreatedMail;
use App\Mail\InvoiceGeneratedMail;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Slab;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AdminCustomerController extends Controller
{
    /**
     * Display a listing of customers for the authenticated admin
     */
    public function index()
    {
        $customers = Customer::where('admin_id', Auth::id())
            ->with(['invoices' => function($query) {
                $query->orderBy('created_at', 'desc');
            }])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        // Update balances for all customers based on latest unpaid invoice
        foreach ($customers as $customer) {
            $latestUnpaid = $customer->getLatestUnpaidInvoice();
            if ($latestUnpaid && $customer->balance != $latestUnpaid->amount) {
                $customer->update(['balance' => $latestUnpaid->amount]);
            } elseif (!$latestUnpaid && $customer->balance != 0) {
                $customer->update(['balance' => 0]);
            }
        }

        return view('admin.customers.index', compact('customers'));
    }

    /**
     * Show invoice history for a customer
     */
    public function showInvoiceHistory($customerId)
    {
        $customer = Customer::where('admin_id', Auth::id())
            ->findOrFail($customerId);

        $invoices = Invoice::where('customer_id', $customer->id)
            ->where('admin_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.customers.invoice-history', compact('customer', 'invoices'));
    }

    /**
     * Store a newly created customer
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone_number' => 'nullable|string|max:20',
            'payee_name' => 'nullable|string|max:255',
            'reference_id' => 'required|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            // Get the admin's prefix number
            $admin = Auth::user();
            $adminPrefix = $admin->prefix_number ?? '';
            
            // Generate unique user number starting from 1000
            if ($adminPrefix) {
                // Admin has prefix - find last customer with same prefix
                $lastCustomer = Customer::where('user_number', 'like', $adminPrefix . '%')
                    ->where('admin_id', Auth::id())
                    ->orderByRaw('CAST(SUBSTRING(user_number, ' . (strlen($adminPrefix) + 1) . ') AS UNSIGNED) DESC')
                    ->first();
                
                if ($lastCustomer) {
                    // Extract the number part after prefix
                    $lastNumber = (int)substr($lastCustomer->user_number, strlen($adminPrefix));
                    $userNumber = $lastNumber + 1;
                } else {
                    $userNumber = 1000;
                }
                
                // Build final user number with prefix (e.g., 35451003)
                $finalUserNumber = $adminPrefix . str_pad((string)$userNumber, 4, '0', STR_PAD_LEFT);
            } else {
                // No prefix - use original logic
                $lastCustomer = Customer::where('admin_id', Auth::id())
                    ->orderByRaw('CAST(user_number AS UNSIGNED) DESC')
                    ->first();
                
                $userNumber = $lastCustomer ? (int)$lastCustomer->user_number + 1 : 1000;
                $finalUserNumber = (string)$userNumber;
            }

            // Ensure user_number is unique within this admin's customers only
            // Different admins can have the same customer number (e.g., both can have 1001)
            // but within a single admin, customer numbers must be unique
            while (Customer::where('admin_id', Auth::id())
                    ->where('user_number', $finalUserNumber)
                    ->exists()) {
                $userNumber++;
                if ($adminPrefix) {
                    $finalUserNumber = $adminPrefix . str_pad((string)$userNumber, 4, '0', STR_PAD_LEFT);
                } else {
                    $finalUserNumber = (string)$userNumber;
                }
            }

            $customer = Customer::create([
                'admin_id' => Auth::id(),
                'name' => $request->name,
                'email' => $request->email,
                'phone_number' => $request->phone_number,
                'payee_name' => $request->payee_name,
                'reference_id' => $request->reference_id,
                'user_number' => $finalUserNumber,
                'balance' => 0, // Default balance set to 0
            ]);

            // Send email to customer
            Mail::to($customer->email)->send(new CustomerCreatedMail($customer));

            DB::commit();

            return redirect()->route('admin.customers.index')
                ->with('success', 'Customer created successfully. Email sent to customer.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create customer: ' . $e->getMessage());
        }
    }

    /**
     * Create invoice automatically with customer balance
     */
    public function createInvoice($customerId)
    {
        $customer = Customer::where('admin_id', Auth::id())
            ->findOrFail($customerId);

        DB::beginTransaction();
        try {
            // Generate invoice number: prefix + customer_number + sequential_invoice_number
            // Customer user_number already contains prefix + customer_number (e.g., 34541001)
            // Find last invoice for this customer to get the next sequential number
            $lastInvoiceForCustomer = Invoice::where('customer_id', $customer->id)
                ->orderBy('created_at', 'desc')
                ->first();
            
            // Extract the invoice number part from the last invoice if it exists
            $invoiceSequence = 1000; // Default starting number
            if ($lastInvoiceForCustomer && $lastInvoiceForCustomer->invoice_number) {
                // Extract the last 4 digits (invoice sequence part) from invoice number
                // Invoice format: prefix+customer_number+invoice_sequence (e.g., 345410011000)
                $lastInvoiceNumber = $lastInvoiceForCustomer->invoice_number;
                $customerNumberLength = strlen($customer->user_number);
                
                // Get the invoice sequence part (last 4 digits)
                if (strlen($lastInvoiceNumber) > $customerNumberLength) {
                    $lastSequence = (int)substr($lastInvoiceNumber, $customerNumberLength);
                    $invoiceSequence = $lastSequence + 1;
                }
            }
            
            // Build invoice number: customer.user_number + invoice_sequence
            $invoiceNumber = $customer->user_number . str_pad((string)$invoiceSequence, 4, '0', STR_PAD_LEFT);

            // Ensure invoice_number is unique (shouldn't happen, but safety check)
            while (Invoice::where('invoice_number', $invoiceNumber)->exists()) {
                $invoiceSequence++;
                $invoiceNumber = $customer->user_number . str_pad((string)$invoiceSequence, 4, '0', STR_PAD_LEFT);
            }

            // Calculate charge based on invoice amount and admin's slabs
            $charge = $this->calculateCharge(Auth::id(), $customer->balance);

            $invoice = Invoice::create([
                'customer_id' => $customer->id,
                'admin_id' => Auth::id(),
                'reference_id' => $customer->reference_id,
                'invoice_number' => $invoiceNumber,
                'amount' => $customer->balance,
                'charge' => $charge,
                'status' => 'pending',
            ]);

            // Balance is already set to invoice amount, no need to update

            // Send email to customer
            Mail::to($customer->email)->send(new InvoiceGeneratedMail($invoice));

            DB::commit();

            // Redirect to invoice view for printing
            return redirect()->route('admin.invoices.show', $invoice->id)
                ->with('success', 'Invoice created successfully. Email sent to customer.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to create invoice: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for generating a custom invoice
     */
    public function showGenerateInvoice($customerId)
    {
        $customer = Customer::where('admin_id', Auth::id())
            ->findOrFail($customerId);

        // Check if customer has previous unpaid invoices
        $unpaidInvoices = Invoice::where('customer_id', $customer->id)
            ->where('admin_id', Auth::id())
            ->where('status', 'pending')
            ->get();

        $hasUnpaidInvoices = $unpaidInvoices->count() > 0;

        return view('admin.customers.generate-invoice', compact('customer', 'hasUnpaidInvoices', 'unpaidInvoices'));
    }

    /**
     * Generate a custom invoice
     */
    public function generateInvoice(Request $request, $customerId)
    {
        $customer = Customer::where('admin_id', Auth::id())
            ->findOrFail($customerId);

        $today = now()->format('Y-m-d');

        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'due_date' => 'required|date|after:' . $today,
            'expiry_date' => 'nullable|date|after_or_equal:due_date',
            'amount_after_due_date' => 'nullable|numeric|min:0',
            'description' => 'nullable|string|max:1000',
            'block_previous_invoices' => 'nullable|boolean',
        ], [
            'due_date.after' => 'The due date must be after the invoice generation date.',
            'expiry_date.after_or_equal' => 'The expiry date must be on or after the due date.',
        ]);

        DB::beginTransaction();
        try {
            // Generate invoice number: prefix + customer_number + sequential_invoice_number
            // Customer user_number already contains prefix + customer_number (e.g., 34541001)
            // Find last invoice for this customer to get the next sequential number
            $lastInvoiceForCustomer = Invoice::where('customer_id', $customer->id)
                ->orderBy('created_at', 'desc')
                ->first();
            
            // Extract the invoice number part from the last invoice if it exists
            $invoiceSequence = 1000; // Default starting number
            if ($lastInvoiceForCustomer && $lastInvoiceForCustomer->invoice_number) {
                // Extract the last 4 digits (invoice sequence part) from invoice number
                // Invoice format: prefix+customer_number+invoice_sequence (e.g., 345410011000)
                $lastInvoiceNumber = $lastInvoiceForCustomer->invoice_number;
                $customerNumberLength = strlen($customer->user_number);
                
                // Get the invoice sequence part (last 4 digits)
                if (strlen($lastInvoiceNumber) > $customerNumberLength) {
                    $lastSequence = (int)substr($lastInvoiceNumber, $customerNumberLength);
                    $invoiceSequence = $lastSequence + 1;
                }
            }
            
            // Build invoice number: customer.user_number + invoice_sequence
            $invoiceNumber = $customer->user_number . str_pad((string)$invoiceSequence, 4, '0', STR_PAD_LEFT);

            // Ensure invoice_number is unique (shouldn't happen, but safety check)
            while (Invoice::where('invoice_number', $invoiceNumber)->exists()) {
                $invoiceSequence++;
                $invoiceNumber = $customer->user_number . str_pad((string)$invoiceSequence, 4, '0', STR_PAD_LEFT);
            }

            // Calculate charge based on invoice amount and admin's slabs
            $charge = $this->calculateCharge(Auth::id(), $request->amount);

            $invoice = Invoice::create([
                'customer_id' => $customer->id,
                'admin_id' => Auth::id(),
                'reference_id' => $customer->reference_id,
                'invoice_number' => $invoiceNumber,
                'amount' => $request->amount,
                'charge' => $charge,
                'due_date' => $request->due_date,
                'expiry_date' => $request->expiry_date,
                'amount_after_due_date' => $request->amount_after_due_date,
                'description' => $request->description,
                'status' => 'pending',
            ]);

            // Block previous unpaid invoices if checkbox is checked
            if ($request->has('block_previous_invoices') && $request->block_previous_invoices) {
                Invoice::where('customer_id', $customer->id)
                    ->where('admin_id', Auth::id())
                    ->where('status', 'pending')
                    ->where('id', '!=', $invoice->id) // Don't block the newly created invoice
                    ->update(['status' => 'blocked']);
            }

            // Update customer balance to the new invoice amount (latest unpaid)
            $customer->update([
                'balance' => $request->amount,
            ]);

            // Send email to customer
            Mail::to($customer->email)->send(new InvoiceGeneratedMail($invoice));

            DB::commit();

            // Redirect to invoice view for printing
            return redirect()->route('admin.invoices.show', $invoice->id)
                ->with('success', 'Invoice generated successfully. Email sent to customer.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to generate invoice: ' . $e->getMessage());
        }
    }

    /**
     * Delete a customer
     */
    public function destroy($id)
    {
        $customer = Customer::where('admin_id', Auth::id())
            ->findOrFail($id);

        $customer->delete();

        return redirect()->route('admin.customers.index')
            ->with('success', 'Customer deleted successfully.');
    }

    /**
     * Show invoice for printing
     */
    public function showInvoice($id)
    {
        $invoice = Invoice::with('customer')
            ->where('admin_id', Auth::id())
            ->findOrFail($id);

        return view('admin.invoices.show', compact('invoice'));
    }

    /**
     * Calculate charge based on payment amount and admin's slabs
     */
    private function calculateCharge($adminId, $amount)
    {
        $slabs = Slab::where('admin_id', $adminId)
            ->orderBy('slab_number')
            ->get();

        if ($slabs->isEmpty()) {
            return 0; // No slabs configured, no charge
        }

        // Find the matching slab
        foreach ($slabs as $slab) {
            if ($amount >= $slab->from_amount) {
                // Check if amount is within this slab's range
                if ($slab->to_amount === null || $amount <= $slab->to_amount) {
                    return $slab->charge;
                }
            }
        }

        // If no matching slab found, return 0
        return 0;
    }
}
