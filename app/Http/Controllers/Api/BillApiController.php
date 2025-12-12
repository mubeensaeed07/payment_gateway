<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BillApiController extends Controller
{
    /**
     * Bill Inquiry API
     * 
     * Parameters:
     * - prefix + customer_number: Returns all invoices for that customer
     * - prefix + customer_number + invoice_number: Returns specific invoice
     */
    public function inquiry(Request $request)
    {
        // Authentication removed for external API access

        $validator = Validator::make($request->all(), [
            'prefix' => 'required|string',
            'customer_number' => 'required|string',
            'invoice_number' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $prefix = $request->prefix;
        $customerNumber = $request->customer_number;
        $invoiceNumber = $request->invoice_number;

        // Build customer number with prefix
        $fullCustomerNumber = $prefix . str_pad($customerNumber, 4, '0', STR_PAD_LEFT);

        // Find customer (search across all admins since we don't have admin_id from auth)
        $customer = Customer::where('user_number', $fullCustomerNumber)
            ->first();

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found'
            ], 404);
        }

        // If invoice number provided, get specific invoice
        if ($invoiceNumber) {
            $fullInvoiceNumber = $fullCustomerNumber . str_pad($invoiceNumber, 4, '0', STR_PAD_LEFT);
            
            $invoice = Invoice::where('customer_id', $customer->id)
                ->where('invoice_number', $fullInvoiceNumber)
                ->with('customer')
                ->first();

            if (!$invoice) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invoice not found'
                ], 404);
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
     * - invoice_number (full: prefix + customer_number + invoice_number)
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

        // Find invoice
        $invoice = Invoice::where('invoice_number', $invoiceNumber)
            ->with('customer')
            ->first();

        if (!$invoice) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice not found'
            ], 404);
        }

        // Check if already paid
        if ($invoice->status === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Invoice is already paid'
            ], 400);
        }

        // Validate amount matches invoice amount
        if (abs($amount - $invoice->amount) > 0.01) {
            return response()->json([
                'success' => false,
                'message' => 'Payment amount does not match invoice amount. Invoice amount: ' . number_format($invoice->amount, 2)
            ], 400);
        }

        DB::beginTransaction();
        try {
            $paymentDate = $request->payment_date ? \Carbon\Carbon::parse($request->payment_date) : now();

            // Update invoice
            $invoice->update([
                'status' => 'paid',
                'paid_at' => $paymentDate,
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
                        'status' => $invoice->status,
                        'paid_at' => $invoice->paid_at->format('Y-m-d H:i:s'),
                        'transaction_id' => $request->transaction_id,
                        'payment_method' => $request->payment_method,
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
}

