<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ExternalProvider;
use App\Models\Invoice;
use App\Models\Customer;
use App\Models\Slab;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class BillApiController extends Controller
{
    /**
     * Bill Inquiry API
     * 
     * Parameters:
     * - invoice_number: Full invoice number (prefix+customer_number+invoice_sequence) OR just prefix+customer_number
     *   Examples:
     *   - Full: "345410011000" (prefix+customer+invoice) → Returns specific invoice
     *   - Partial: "34541001" (prefix+customer) → Returns all invoices for that customer
     */
    public function inquiry(Request $request)
    {
        // Authentication removed for external API access

        $validator = Validator::make($request->all(), [
            'invoice_number' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $inputNumber = $request->invoice_number;
        $inputLength = strlen($inputNumber);

        // Check if invoice number ends with "02" (external provider request)
        $isExternalProvider = false;
        $originalInvoiceNumber = $inputNumber;
        
        if ($inputLength >= 2 && substr($inputNumber, -2) === '02') {
            $isExternalProvider = true;
            // Strip "02" from the end
            $inputNumber = substr($inputNumber, 0, -2);
            $inputLength = strlen($inputNumber);
        }

        // First, try to find invoice by full invoice number
        $invoice = Invoice::where('invoice_number', $inputNumber)
            ->with('customer')
            ->first();

        if ($invoice) {
            // Found specific invoice
            // If this is an external provider request, route to their API
            if ($isExternalProvider) {
                // Extract prefix and customer number from customer.user_number
                $customerNumber = $invoice->customer->user_number;
                // Try to extract prefix (first 4 digits) and customer number (rest)
                // This is a best guess - we'll send what we have
                return $this->routeToExternalProvider($invoice->admin_id, 'inquiry', [
                    'invoice_number' => $originalInvoiceNumber, // Send original with "02"
                ]);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'invoice' => [
                        'invoice_number' => $invoice->invoice_number,
                        'customer_name' => $invoice->customer->name,
                        'customer_number' => $invoice->customer->user_number,
                        'customer_email' => $invoice->customer->email,
                        'amount' => (float)$invoice->amount,
                        'charge' => (float)($invoice->charge ?? 0),
                        'total' => (float)($invoice->amount + ($invoice->charge ?? 0)),
                        'status' => $invoice->status,
                        'due_date' => $invoice->due_date ? $invoice->due_date->format('Y-m-d') : null,
                        'expiry_date' => $invoice->expiry_date ? $invoice->expiry_date->format('Y-m-d') : null,
                        'paid_at' => $invoice->paid_at ? $invoice->paid_at->format('Y-m-d H:i:s') : null,
                        'created_at' => $invoice->created_at->format('Y-m-d H:i:s'),
                        'description' => $invoice->description,
                    ]
                ]
            ]);
        }

        // If invoice not found, try to find customer by treating input as customer number
        $customer = Customer::where('user_number', $inputNumber)->first();

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice or customer not found'
            ], 404);
        }

        // If this is an external provider request and we found customer, route to their API
        if ($isExternalProvider) {
            return $this->routeToExternalProvider($customer->admin_id, 'inquiry', [
                'invoice_number' => $originalInvoiceNumber, // Send original with "02"
            ]);
        }

        // Get all invoices for customer
        $invoices = Invoice::where('customer_id', $customer->id)
            ->with('customer')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'customer' => [
                    'name' => $customer->name,
                    'email' => $customer->email,
                    'customer_number' => $customer->user_number,
                    'reference_id' => $customer->reference_id,
                ],
                'invoices' => $invoices->map(function ($invoice) {
                    return [
                        'invoice_number' => $invoice->invoice_number,
                        'amount' => (float)$invoice->amount,
                        'charge' => (float)($invoice->charge ?? 0),
                        'total' => (float)($invoice->amount + ($invoice->charge ?? 0)),
                        'status' => $invoice->status,
                        'due_date' => $invoice->due_date ? $invoice->due_date->format('Y-m-d') : null,
                        'expiry_date' => $invoice->expiry_date ? $invoice->expiry_date->format('Y-m-d') : null,
                        'paid_at' => $invoice->paid_at ? $invoice->paid_at->format('Y-m-d H:i:s') : null,
                        'created_at' => $invoice->created_at->format('Y-m-d H:i:s'),
                    ];
                })
            ]
        ]);
    }

    /**
     * Bill Payment API
     * 
     * Parameters:
     * - invoice_number: Full invoice number (prefix+customer_number+invoice_sequence) OR just prefix+customer_number
     *   Examples:
     *   - Full: "345410011000" (prefix+customer+invoice) → Pays specific invoice
     *   - Partial: "34541001" (prefix+customer) → Pays latest unpaid invoice for that customer
     * - amount (must match invoice amount)
     * - transaction_id (optional)
     * - payment_date (optional)
     * - payment_method (optional)
     */
    public function payment(Request $request)
    {
        // Authentication removed for external API access

        $validator = Validator::make($request->all(), [
            'invoice_number' => 'required|string',
            'amount' => 'required|numeric|min:0.01',
            'transaction_id' => 'nullable|string|max:255',
            'payment_date' => 'nullable|date',
            'payment_method' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $invoiceNumber = $request->invoice_number;
        $amount = $request->amount;

        // Check if invoice number ends with "02" (external provider request)
        $isExternalProvider = false;
        $originalInvoiceNumber = $invoiceNumber;
        
        if (strlen($invoiceNumber) >= 2 && substr($invoiceNumber, -2) === '02') {
            $isExternalProvider = true;
            // Strip "02" from the end to get our internal invoice number
            $invoiceNumber = substr($invoiceNumber, 0, -2);
        }

        // First, try to find invoice by full invoice number (prefix+customer_number+invoice_sequence)
        $invoice = Invoice::where('invoice_number', $invoiceNumber)
            ->with('customer')
            ->first();

        // If invoice not found, try to find customer by treating input as customer number
        if (!$invoice) {
            $customer = Customer::where('user_number', $invoiceNumber)->first();
            
            if (!$customer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invoice or customer not found. Please provide the full invoice number (prefix+customer_number+invoice_sequence) or customer number (prefix+customer_number).'
                ], 404);
            }

            // If this is an external provider request and we found customer, route to their API
            if ($isExternalProvider) {
                return $this->routeToExternalProvider($customer->admin_id, 'payment', [
                    'invoice_number' => $originalInvoiceNumber, // Send original with "02"
                    'amount' => $amount,
                    'transaction_id' => $request->transaction_id,
                    'payment_date' => $request->payment_date,
                    'payment_method' => $request->payment_method,
                ]);
            }

            // Get the latest unpaid invoice for this customer
            $invoice = $customer->getLatestUnpaidInvoice();
            
            if (!$invoice) {
                return response()->json([
                    'success' => false,
                    'message' => 'No unpaid invoice found for this customer.'
                ], 404);
            }
        }

        // If this is an external provider request, route to their API
        if ($isExternalProvider) {
            return $this->routeToExternalProvider($invoice->admin_id, 'payment', [
                'invoice_number' => $originalInvoiceNumber, // Send original with "02"
                'amount' => $amount,
                'transaction_id' => $request->transaction_id,
                'payment_date' => $request->payment_date,
                'payment_method' => $request->payment_method,
            ]);
        }

        // Check if already paid
        if ($invoice->status === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Invoice is already paid'
            ], 400);
        }

        DB::beginTransaction();
        try {
            $paymentDate = $request->payment_date ? \Carbon\Carbon::parse($request->payment_date) : now();

            // Calculate charge based on slabs if not already set
            $charge = $invoice->charge ?? 0;
            if ($charge == 0) {
                $charge = $this->calculateCharge($invoice->admin_id, $invoice->amount);
                // Update invoice with calculated charge
                $invoice->update(['charge' => $charge]);
            }

            // Check if payment is after due date and include amount_after_due_date
            $dueDateAmount = 0;
            if ($invoice->due_date && $invoice->amount_after_due_date && $invoice->amount_after_due_date > 0) {
                // Compare payment date with due date
                $dueDate = \Carbon\Carbon::parse($invoice->due_date)->startOfDay();
                $paymentDateOnly = $paymentDate->copy()->startOfDay();
                
                if ($paymentDateOnly->gt($dueDate)) {
                    // Payment is after due date, include the after-due-date amount
                    $dueDateAmount = $invoice->amount_after_due_date;
                }
            }

            // Calculate total: invoice amount + charge + due date amount (if applicable)
            $invoiceTotal = $invoice->amount + $charge + $dueDateAmount;
            
            // Validate amount matches invoice total
            if (abs($amount - $invoiceTotal) > 0.01) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Payment amount does not match invoice total. Invoice amount: ' . number_format($invoice->amount, 2) . 
                                ($charge > 0 ? ' + Charge: ' . number_format($charge, 2) : '') . 
                                ($dueDateAmount > 0 ? ' + After Due Date Amount: ' . number_format($dueDateAmount, 2) : '') .
                                ' = Total: ' . number_format($invoiceTotal, 2)
                ], 400);
            }

            // Store bank information from payment_method (API sends payment_method as bank name)
            $bank = $request->payment_method ?? null;

            // Update invoice
            $invoice->update([
                'status' => 'paid',
                'paid_at' => $paymentDate,
                'bank' => $bank,
            ]);

            // Update customer balance to next unpaid invoice (if any)
            $customer = $invoice->customer;
            $nextUnpaidInvoice = $customer->getLatestUnpaidInvoice();
            $customer->update([
                'balance' => $nextUnpaidInvoice ? $nextUnpaidInvoice->amount : 0,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payment processed successfully',
                'data' => [
                    'invoice' => [
                        'invoice_number' => $invoice->invoice_number,
                        'customer_name' => $invoice->customer->name,
                        'customer_number' => $invoice->customer->user_number,
                        'amount' => (float)$invoice->amount,
                        'charge' => (float)$charge,
                        'due_date_amount' => (float)$dueDateAmount,
                        'total' => (float)$invoiceTotal,
                        'status' => $invoice->status,
                        'paid_at' => $invoice->paid_at->format('Y-m-d H:i:s'),
                        'transaction_id' => $request->transaction_id,
                        'payment_method' => $request->payment_method,
                        'bank' => $bank,
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Payment processing failed: ' . $e->getMessage()
            ], 500);
        }
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

    /**
     * Route request to external provider API
     */
    private function routeToExternalProvider($adminId, $type, $data)
    {
        // Get external provider credentials for this admin
        $externalProvider = ExternalProvider::where('admin_id', $adminId)->first();

        if (!$externalProvider) {
            return response()->json([
                'success' => false,
                'message' => 'External provider not configured for this admin'
            ], 404);
        }

        // Determine API URL based on type
        $apiUrl = $type === 'inquiry' 
            ? $externalProvider->bill_enquiry_url 
            : $externalProvider->bill_payment_url;

        try {
            // Prepare request data with authentication
            $requestData = array_merge($data, [
                'username' => $externalProvider->username,
                'password' => $externalProvider->password,
            ]);

            // Make HTTP request to external provider API
            $response = Http::timeout(30)->post($apiUrl, $requestData);

            // Return the external provider's response
            return response()->json(
                $response->json(),
                $response->status()
            );
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to connect to external provider: ' . $e->getMessage()
            ], 500);
        }
    }
}

