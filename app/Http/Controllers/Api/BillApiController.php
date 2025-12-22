<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ExternalProvider;
use App\Models\ExternalProviderLog;
use App\Models\ApiLog;
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
                return $this->routeToExternalProvider(
                    $invoice->admin_id, 
                    'inquiry', 
                    [
                        'invoice_number' => $originalInvoiceNumber, // Send original with "02"
                    ]
                );
            }

            // Log internal API call
            $responseData = [
                'success' => true,
                'data' => [
                    'invoice' => [
                        'invoice_number' => $invoice->invoice_number,
                        'customer_name' => $invoice->customer->name,
                        'customer_number' => $invoice->customer->user_number,
                        'customer_email' => $invoice->customer->email,
                        'amount' => (float)$invoice->amount,
                        'admin_charge' => (float)($invoice->charge ?? 0),
                        'onelink_fee' => (float)($invoice->onelink_fee ?? 0),
                        'total_charge' => (float)(($invoice->charge ?? 0) + ($invoice->onelink_fee ?? 0)),
                        'total' => (float)($invoice->amount + ($invoice->charge ?? 0) + ($invoice->onelink_fee ?? 0)),
                        'status' => $invoice->status,
                        'due_date' => $invoice->due_date ? $invoice->due_date->format('Y-m-d') : null,
                        'expiry_date' => $invoice->expiry_date ? $invoice->expiry_date->format('Y-m-d') : null,
                        'paid_at' => $invoice->paid_at ? $invoice->paid_at->format('Y-m-d H:i:s') : null,
                        'created_at' => $invoice->created_at->format('Y-m-d H:i:s'),
                        'description' => $invoice->description,
                    ]
                ]
            ];

            $this->logInternalApiRequest(
                $invoice->admin_id,
                'inquiry',
                ['invoice_number' => $inputNumber],
                $responseData,
                200,
                true,
                null,
                $invoice->customer->name,
                $invoice->customer->user_number
            );

            return response()->json($responseData);
        }

        // If invoice not found, try to find customer by treating input as customer number
        $customer = Customer::where('user_number', $inputNumber)->first();

        if (!$customer) {
            $errorResponse = [
                'success' => false,
                'message' => 'Invoice or customer not found'
            ];
            
            // Try to determine admin_id from input number
            $adminId = null;
            // Try to find admin by checking if input matches any admin's prefix pattern
            // For now, we'll skip logging if we can't determine admin_id
            
            return response()->json($errorResponse, 404);
        }

        // If this is an external provider request and we found customer, route to their API
        if ($isExternalProvider) {
            return $this->routeToExternalProvider(
                $customer->admin_id, 
                'inquiry', 
                [
                    'invoice_number' => $originalInvoiceNumber, // Send original with "02"
                ]
            );
        }

        // Get all invoices for customer
        $invoices = Invoice::where('customer_id', $customer->id)
            ->with('customer')
            ->orderBy('created_at', 'desc')
            ->get();

        $responseData = [
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
                        'admin_charge' => (float)($invoice->charge ?? 0),
                        'onelink_fee' => (float)($invoice->onelink_fee ?? 0),
                        'total_charge' => (float)(($invoice->charge ?? 0) + ($invoice->onelink_fee ?? 0)),
                        'total' => (float)($invoice->amount + ($invoice->charge ?? 0) + ($invoice->onelink_fee ?? 0)),
                        'status' => $invoice->status,
                        'due_date' => $invoice->due_date ? $invoice->due_date->format('Y-m-d') : null,
                        'expiry_date' => $invoice->expiry_date ? $invoice->expiry_date->format('Y-m-d') : null,
                        'paid_at' => $invoice->paid_at ? $invoice->paid_at->format('Y-m-d H:i:s') : null,
                        'created_at' => $invoice->created_at->format('Y-m-d H:i:s'),
                    ];
                })
            ]
        ];

        // Log internal API call
        $this->logInternalApiRequest(
            $customer->admin_id,
            'inquiry',
            ['invoice_number' => $inputNumber],
            $responseData,
            200,
            true,
            null,
            $customer->name,
            $customer->user_number
        );

        return response()->json($responseData);
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
                $errorResponse = [
                    'success' => false,
                    'message' => 'Invoice or customer not found. Please provide the full invoice number (prefix+customer_number+invoice_sequence) or customer number (prefix+customer_number).'
                ];
                
                // Try to determine admin_id - skip logging if we can't determine
                
                return response()->json($errorResponse, 404);
            }

            // If this is an external provider request and we found customer, route to their API
            if ($isExternalProvider) {
                return $this->routeToExternalProvider(
                    $customer->admin_id, 
                    'payment', 
                    [
                        'invoice_number' => $originalInvoiceNumber, // Send original with "02"
                        'amount' => $amount,
                        'transaction_id' => $request->transaction_id,
                        'payment_date' => $request->payment_date,
                        'payment_method' => $request->payment_method,
                    ]
                );
            }

            // Get the latest unpaid invoice for this customer
            $invoice = $customer->getLatestUnpaidInvoice();
            
            if (!$invoice) {
                $errorResponse = [
                    'success' => false,
                    'message' => 'No unpaid invoice found for this customer.'
                ];
                
                // Log failed payment
                $this->logInternalApiRequest(
                    $customer->admin_id,
                    'payment',
                    $request->all(),
                    $errorResponse,
                    404,
                    false,
                    'No unpaid invoice found for this customer',
                    $customer->name,
                    $customer->user_number
                );
                
                return response()->json($errorResponse, 404);
            }
        }

        // If this is an external provider request, route to their API
        if ($isExternalProvider) {
            return $this->routeToExternalProvider(
                $invoice->admin_id, 
                'payment', 
                [
                    'invoice_number' => $originalInvoiceNumber, // Send original with "02"
                    'amount' => $amount,
                    'transaction_id' => $request->transaction_id,
                    'payment_date' => $request->payment_date,
                    'payment_method' => $request->payment_method,
                ]
            );
        }

        // Check if already paid
        if ($invoice->status === 'paid') {
            $errorResponse = [
                'success' => false,
                'message' => 'Invoice is already paid'
            ];
            
            // Log failed payment
            $this->logInternalApiRequest(
                $invoice->admin_id,
                'payment',
                $request->all(),
                $errorResponse,
                400,
                false,
                'Invoice is already paid',
                $invoice->customer->name,
                $invoice->customer->user_number
            );
            
            return response()->json($errorResponse, 400);
        }

        DB::beginTransaction();
        try {
            $paymentDate = $request->payment_date ? \Carbon\Carbon::parse($request->payment_date) : now();

            // Calculate charges based on slabs if not already set
            $charge = $invoice->charge ?? 0;
            $onelinkFee = $invoice->onelink_fee ?? 0;
            
            if ($charge == 0 && $onelinkFee == 0) {
                $charges = $this->calculateCharges($invoice->admin_id, $invoice->amount);
                $charge = $charges['admin_charge'];
                $onelinkFee = $charges['onelink_fee'];
                // Update invoice with calculated charges
                $invoice->update([
                    'charge' => $charge,
                    'onelink_fee' => $onelinkFee
                ]);
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

            // Calculate total: invoice amount + admin charge + 1Link fee + due date amount (if applicable)
            $totalCharge = $charge + $onelinkFee;
            $invoiceTotal = $invoice->amount + $totalCharge + $dueDateAmount;
            
            // Validate amount matches invoice total
            if (abs($amount - $invoiceTotal) > 0.01) {
                DB::rollBack();
                
                $errorMessage = 'Payment amount does not match invoice total. Invoice amount: ' . number_format($invoice->amount, 2) . 
                                ($charge > 0 ? ' + Admin Charge: ' . number_format($charge, 2) : '') . 
                                ($onelinkFee > 0 ? ' + 1Link Fee: ' . number_format($onelinkFee, 2) : '') . 
                                ($dueDateAmount > 0 ? ' + After Due Date Amount: ' . number_format($dueDateAmount, 2) : '') .
                                ' = Total: ' . number_format($invoiceTotal, 2);
                
                $errorResponse = [
                    'success' => false,
                    'message' => $errorMessage
                ];
                
                // Log failed payment
                $this->logInternalApiRequest(
                    $invoice->admin_id,
                    'payment',
                    $request->all(),
                    $errorResponse,
                    400,
                    false,
                    $errorMessage,
                    $invoice->customer->name,
                    $invoice->customer->user_number
                );
                
                return response()->json($errorResponse, 400);
            }

            // Store bank information from payment_method (API sends payment_method as bank name)
            $bank = $request->payment_method ?? null;

            // Update invoice - mark as paid via API
            $invoice->update([
                'status' => 'paid',
                'paid_at' => $paymentDate,
                'bank' => $bank,
                'paid_via_api' => true, // Mark that this payment was made via API
            ]);

            // Update customer balance to next unpaid invoice (if any)
            $customer = $invoice->customer;
            $nextUnpaidInvoice = $customer->getLatestUnpaidInvoice();
            $customer->update([
                'balance' => $nextUnpaidInvoice ? $nextUnpaidInvoice->amount : 0,
            ]);

            DB::commit();

            $responseData = [
                'success' => true,
                'message' => 'Payment processed successfully',
                'data' => [
                    'invoice' => [
                        'invoice_number' => $invoice->invoice_number,
                        'customer_name' => $invoice->customer->name,
                        'customer_number' => $invoice->customer->user_number,
                        'amount' => (float)$invoice->amount,
                        'admin_charge' => (float)$charge,
                        'onelink_fee' => (float)$onelinkFee,
                        'total_charge' => (float)$totalCharge,
                        'due_date_amount' => (float)$dueDateAmount,
                        'total' => (float)$invoiceTotal,
                        'status' => $invoice->status,
                        'paid_at' => $invoice->paid_at->format('Y-m-d H:i:s'),
                        'transaction_id' => $request->transaction_id,
                        'payment_method' => $request->payment_method,
                        'bank' => $bank,
                    ]
                ]
            ];

            // Log internal API call
            $this->logInternalApiRequest(
                $invoice->admin_id,
                'payment',
                $request->all(),
                $responseData,
                200,
                true,
                null,
                $invoice->customer->name,
                $invoice->customer->user_number
            );

            return response()->json($responseData);
        } catch (\Exception $e) {
            DB::rollBack();
            
            $errorResponse = [
                'success' => false,
                'message' => 'Payment processing failed: ' . $e->getMessage()
            ];
            
            // Log failed payment if we have invoice
            if (isset($invoice)) {
                $this->logInternalApiRequest(
                    $invoice->admin_id,
                    'payment',
                    $request->all(),
                    $errorResponse,
                    500,
                    false,
                    $e->getMessage(),
                    $invoice->customer->name ?? null,
                    $invoice->customer->user_number ?? null
                );
            }
            
            return response()->json($errorResponse, 500);
        }
    }

    /**
     * Calculate charges (admin charge + 1Link fee) based on payment amount and admin's slabs
     * Returns array with 'admin_charge' and 'onelink_fee'
     */
    private function calculateCharges($adminId, $amount)
    {
        $slabs = Slab::where('admin_id', $adminId)
            ->orderBy('slab_number')
            ->get();

        if ($slabs->isEmpty()) {
            return ['admin_charge' => 0, 'onelink_fee' => 0]; // No slabs configured, no charges
        }

        // Find the matching slab
        foreach ($slabs as $slab) {
            if ($amount >= $slab->from_amount) {
                // Check if amount is within this slab's range
                if ($slab->to_amount === null || $amount <= $slab->to_amount) {
                    return [
                        'admin_charge' => $slab->charge ?? 0,
                        'onelink_fee' => $slab->onelink_fee ?? 0
                    ];
                }
            }
        }

        // If no matching slab found, return 0
        return ['admin_charge' => 0, 'onelink_fee' => 0];
    }

    /**
     * Route request to external provider API
     */
    private function routeToExternalProvider($adminId, $type, $data, $customerName = null, $customerNumber = null)
    {
        // Get external provider credentials for this admin
        $externalProvider = ExternalProvider::where('admin_id', $adminId)->first();

        if (!$externalProvider) {
            // Log the failure (no customer info available since external provider not configured)
            $this->logExternalProviderRequest($adminId, $type, $data, null, null, false, 'External provider not configured for this admin', null, null, null);
            
            return response()->json([
                'success' => false,
                'message' => 'External provider not configured for this admin'
            ], 404);
        }

        // Determine API URL based on type
        $apiUrl = $type === 'inquiry' 
            ? $externalProvider->bill_enquiry_url 
            : $externalProvider->bill_payment_url;

        // For external provider requests, DO NOT look up customer info from local database
        // We will extract it from the external provider's response instead
        // This ensures we log what the external provider actually returned, not what's in our DB

        // Prepare request data with authentication (for logging, we'll log without password)
        $requestDataForLog = $data; // Don't include password in log
        $requestData = array_merge($data, [
            'username' => $externalProvider->username,
            'password' => $externalProvider->password,
        ]);

        try {
            // Make HTTP request to external provider API
            $response = Http::timeout(30)->post($apiUrl, $requestData);
            
            $responseStatus = $response->status();
            $responseBody = $response->json();
            
            // If response is not JSON, try to get as string
            if ($responseBody === null) {
                $responseBody = ['raw_response' => $response->body()];
            }
            
            // Check if response is successful (both HTTP status and response body success field)
            $httpSuccess = $responseStatus >= 200 && $responseStatus < 300;
            $bodySuccess = isset($responseBody['success']) ? (bool)$responseBody['success'] : true;
            $isSuccessful = $httpSuccess && $bodySuccess;
            
            // Extract customer information from external provider's response (even if failed, try to extract)
            $responseCustomerName = null;
            $responseCustomerNumber = null;
            
            if ($responseBody) {
                $extractedInfo = $this->extractCustomerInfoFromResponse($responseBody, $type);
                $responseCustomerName = $extractedInfo['customer_name'] ?? null;
                $responseCustomerNumber = $extractedInfo['customer_number'] ?? null;
            }
            
            // If customer number not found in response, try to extract from invoice number (remove "02" suffix)
            if (!$responseCustomerNumber && isset($data['invoice_number'])) {
                $invoiceNum = $data['invoice_number'];
                if (strlen($invoiceNum) >= 2 && substr($invoiceNum, -2) === '02') {
                    // Remove "02" suffix to get potential customer number
                    $potentialCustomerNumber = substr($invoiceNum, 0, -2);
                    // Only use if it looks like a valid customer number (has reasonable length)
                    if (strlen($potentialCustomerNumber) >= 4) {
                        $responseCustomerNumber = $potentialCustomerNumber;
                    }
                }
            }
            
            // Determine error message if failed
            $errorMessage = null;
            if (!$isSuccessful) {
                if (isset($responseBody['message'])) {
                    $errorMessage = $responseBody['message'];
                } elseif (isset($responseBody['error'])) {
                    $errorMessage = $responseBody['error'];
                } elseif (!$httpSuccess) {
                    $errorMessage = 'HTTP Error: ' . $responseStatus;
                } else {
                    $errorMessage = 'Request failed';
                }
            }
            
            // Log successful or failed request with customer info from external provider's response
            $this->logExternalProviderRequest(
                $adminId,
                $type,
                $requestDataForLog,
                $responseBody,
                $responseStatus,
                $isSuccessful,
                $errorMessage,
                $apiUrl,
                $responseCustomerName, // Use customer name from external provider's response
                $responseCustomerNumber // Use customer number from external provider's response
            );

            // Return the external provider's response
            return response()->json(
                $responseBody,
                $responseStatus
            );
        } catch (\Exception $e) {
            // Log the exception (no customer info available since request failed)
            $this->logExternalProviderRequest(
                $adminId,
                $type,
                $requestDataForLog,
                null,
                500,
                false,
                'Failed to connect to external provider: ' . $e->getMessage(),
                $apiUrl,
                null, // No customer info available on error
                null  // No customer info available on error
            );
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to connect to external provider: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Extract customer information from external provider's response
     */
    private function extractCustomerInfoFromResponse($responseBody, $type)
    {
        $customerName = null;
        $customerNumber = null;

        if (!is_array($responseBody)) {
            return ['customer_name' => null, 'customer_number' => null];
        }

        // Try various possible response structures
        // Common patterns:
        // 1. data.customer.name / data.customer.customer_number
        // 2. data.invoice.customer_name / data.invoice.customer_number
        // 3. data.invoices[0].customer_name (for multiple invoices)
        // 4. customer.name / customer.customer_number
        // 5. invoice.customer_name / invoice.customer_number

        if (isset($responseBody['data'])) {
            $data = $responseBody['data'];
            
            // Check for customer object
            if (isset($data['customer']) && is_array($data['customer'])) {
                $customer = $data['customer'];
                $customerName = $customer['name'] ?? $customer['customer_name'] ?? $customer['customerName'] ?? null;
                $customerNumber = $customer['customer_number'] ?? $customer['customer_no'] ?? $customer['customerNumber'] ?? $customer['number'] ?? $customer['customerNumber'] ?? null;
            }
            
            // Check for invoice object with customer info
            if (isset($data['invoice']) && is_array($data['invoice'])) {
                $invoice = $data['invoice'];
                if (!$customerName) {
                    $customerName = $invoice['customer_name'] ?? $invoice['customerName'] ?? $invoice['name'] ?? null;
                }
                if (!$customerNumber) {
                    $customerNumber = $invoice['customer_number'] ?? $invoice['customer_no'] ?? $invoice['customerNumber'] ?? null;
                }
            }
            
            // Check for invoices array (multiple invoices)
            if (isset($data['invoices']) && is_array($data['invoices']) && count($data['invoices']) > 0) {
                $firstInvoice = $data['invoices'][0];
                if (!$customerName && isset($firstInvoice['customer_name'])) {
                    $customerName = $firstInvoice['customer_name'];
                }
                if (!$customerNumber && isset($firstInvoice['customer_number'])) {
                    $customerNumber = $firstInvoice['customer_number'];
                }
            }
        }

        // Check at root level
        if (!$customerName && isset($responseBody['customer_name'])) {
            $customerName = $responseBody['customer_name'];
        }
        if (!$customerName && isset($responseBody['customerName'])) {
            $customerName = $responseBody['customerName'];
        }
        if (!$customerName && isset($responseBody['name'])) {
            $customerName = $responseBody['name'];
        }
        // Check for alternative field names (like consumer_Detail, consumer_name, etc.)
        if (!$customerName && isset($responseBody['consumer_Detail'])) {
            $customerName = trim($responseBody['consumer_Detail']);
        }
        if (!$customerName && isset($responseBody['consumer_detail'])) {
            $customerName = trim($responseBody['consumer_detail']);
        }
        if (!$customerName && isset($responseBody['consumer_name'])) {
            $customerName = trim($responseBody['consumer_name']);
        }
        if (!$customerName && isset($responseBody['consumerName'])) {
            $customerName = trim($responseBody['consumerName']);
        }
        
        if (!$customerNumber && isset($responseBody['customer_number'])) {
            $customerNumber = $responseBody['customer_number'];
        }
        if (!$customerNumber && isset($responseBody['customerNumber'])) {
            $customerNumber = $responseBody['customerNumber'];
        }
        if (!$customerNumber && isset($responseBody['customer_no'])) {
            $customerNumber = $responseBody['customer_no'];
        }
        if (!$customerNumber && isset($responseBody['customerNo'])) {
            $customerNumber = $responseBody['customerNo'];
        }
        // Check for alternative field names
        if (!$customerNumber && isset($responseBody['consumer_number'])) {
            $customerNumber = $responseBody['consumer_number'];
        }
        if (!$customerNumber && isset($responseBody['consumerNumber'])) {
            $customerNumber = $responseBody['consumerNumber'];
        }

        return [
            'customer_name' => $customerName ? trim($customerName) : null,
            'customer_number' => $customerNumber ? trim($customerNumber) : null
        ];
    }

    /**
     * Log external provider API request
     */
    private function logExternalProviderRequest($adminId, $type, $requestData, $responseData, $responseStatus, $isSuccessful, $errorMessage, $apiUrl, $customerName = null, $customerNumber = null)
    {
        try {
            $invoiceNumber = $requestData['invoice_number'] ?? null;
            
            ExternalProviderLog::create([
                'admin_id' => $adminId,
                'api_type' => $type,
                'invoice_number' => $invoiceNumber,
                'customer_name' => $customerName,
                'customer_number' => $customerNumber,
                'request_data' => $requestData,
                'response_data' => $responseData,
                'response_status' => $responseStatus,
                'is_successful' => $isSuccessful,
                'error_message' => $errorMessage,
                'external_provider_url' => $apiUrl,
            ]);
        } catch (\Exception $e) {
            // Silently fail logging to not break the main flow
            \Log::error('Failed to log external provider request: ' . $e->getMessage());
        }
    }

    /**
     * Log internal API request (own database)
     */
    private function logInternalApiRequest($adminId, $type, $requestData, $responseData, $responseStatus, $isSuccessful, $errorMessage = null, $customerName = null, $customerNumber = null)
    {
        try {
            $invoiceNumber = $requestData['invoice_number'] ?? null;
            
            ApiLog::create([
                'admin_id' => $adminId,
                'api_type' => $type,
                'invoice_number' => $invoiceNumber,
                'customer_name' => $customerName,
                'customer_number' => $customerNumber,
                'request_data' => $requestData,
                'response_data' => $responseData,
                'response_status' => $responseStatus,
                'is_successful' => $isSuccessful,
                'error_message' => $errorMessage,
            ]);
        } catch (\Exception $e) {
            // Silently fail logging to not break the main flow
            \Log::error('Failed to log internal API request: ' . $e->getMessage());
        }
    }
}

