<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $invoice->invoice_number }} - {{ config('app.name') }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            background: #f5f5f5;
            padding: 20px;
            color: #333;
        }
        
        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        .invoice-header {
            border-bottom: 3px solid #2130B8;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .invoice-header h1 {
            color: #2130B8;
            font-size: 32px;
            margin-bottom: 10px;
        }
        
        .invoice-header p {
            color: #666;
            font-size: 14px;
        }
        
        .invoice-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        
        .invoice-info-left,
        .invoice-info-right {
            flex: 1;
            min-width: 250px;
        }
        
        .invoice-info-section {
            margin-bottom: 20px;
        }
        
        .invoice-info-section h3 {
            color: #2130B8;
            font-size: 16px;
            margin-bottom: 10px;
            border-bottom: 2px solid #2130B8;
            padding-bottom: 5px;
        }
        
        .invoice-info-section p {
            margin: 5px 0;
            font-size: 14px;
            color: #333;
        }
        
        .invoice-info-section strong {
            color: #2130B8;
        }
        
        .invoice-table {
            width: 100%;
            border-collapse: collapse;
            margin: 30px 0;
        }
        
        .invoice-table th {
            background: #2130B8;
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: bold;
        }
        
        .invoice-table td {
            padding: 15px;
            border-bottom: 1px solid #ddd;
        }
        
        .invoice-table tr:last-child td {
            border-bottom: none;
        }
        
        .invoice-table .text-right {
            text-align: right;
        }
        
        .invoice-total {
            margin-top: 20px;
            text-align: right;
        }
        
        .invoice-total-row {
            display: flex;
            justify-content: flex-end;
            margin: 10px 0;
            font-size: 16px;
        }
        
        .invoice-total-label {
            width: 200px;
            text-align: right;
            padding-right: 20px;
            font-weight: bold;
            color: #333;
        }
        
        .invoice-total-amount {
            width: 150px;
            text-align: right;
            font-weight: bold;
            color: #2130B8;
            font-size: 20px;
        }
        
        .invoice-footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #ddd;
            text-align: center;
            color: #666;
            font-size: 12px;
        }
        
        .invoice-status {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 12px;
        }
        
        .invoice-status.pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .invoice-status.paid {
            background: #d4edda;
            color: #155724;
        }
        
        .invoice-status.cancelled {
            background: #f8d7da;
            color: #721c24;
        }
        
        .invoice-status.blocked {
            background: #6c757d;
            color: #ffffff;
        }
        
        .print-button {
            text-align: center;
            margin: 20px 0;
        }
        
        .print-button button {
            background: #2130B8;
            color: white;
            border: none;
            padding: 12px 30px;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        
        .print-button button:hover {
            background: #1a2580;
        }
        
        @media print {
            body {
                background: white;
                padding: 0;
            }
            
            .print-button {
                display: none;
            }
            
            .invoice-container {
                box-shadow: none;
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    @if(session('success'))
        <div style="max-width: 800px; margin: 20px auto; padding: 15px; background: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 5px;">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div style="max-width: 800px; margin: 20px auto; padding: 15px; background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 5px;">
            {{ session('error') }}
        </div>
    @endif

    <div class="print-button">
        <button onclick="window.print()">🖨️ Print Invoice</button>
        <a href="{{ route('admin.customers.index') }}" style="margin-left: 10px; padding: 12px 30px; background: #6c757d; color: white; text-decoration: none; border-radius: 5px; display: inline-block;">← Back to Customers</a>
    </div>
    
    <div class="invoice-container">
        <div class="invoice-header">
            <h1>INVOICE</h1>
            <p>Invoice #{{ $invoice->invoice_number }}</p>
            <p>Date: {{ $invoice->created_at->format('F d, Y') }}</p>
        </div>
        
        <div class="invoice-info">
            <div class="invoice-info-left">
                <div class="invoice-info-section">
                    <h3>Bill To:</h3>
                    <p><strong>Name:</strong> {{ $invoice->customer->name }}</p>
                    <p><strong>Email:</strong> {{ $invoice->customer->email }}</p>
                    <p><strong>User Number:</strong> {{ $invoice->customer->user_number }}</p>
                    <p><strong>Reference ID:</strong> {{ $invoice->reference_id }}</p>
                </div>
            </div>
            
            <div class="invoice-info-right">
                <div class="invoice-info-section">
                    <h3>Invoice Details:</h3>
                    <p><strong>Invoice Number:</strong> {{ $invoice->invoice_number }}</p>
                    <p><strong>Status:</strong> <span class="invoice-status {{ $invoice->status }}">{{ $invoice->status }}</span></p>
                    <p><strong>Issue Date:</strong> {{ $invoice->created_at->format('F d, Y') }}</p>
                    @if($invoice->due_date)
                        <p><strong>Due Date:</strong> {{ $invoice->due_date->format('F d, Y') }}</p>
                    @else
                        <p><strong>Due Date:</strong> {{ $invoice->created_at->copy()->addDays(30)->format('F d, Y') }}</p>
                    @endif
                    @if($invoice->expiry_date)
                        <p><strong>Expiry Date:</strong> {{ $invoice->expiry_date->format('F d, Y') }}</p>
                    @endif
                    @if($invoice->paid_at)
                        <p><strong>Paid Date:</strong> {{ $invoice->paid_at->format('F d, Y') }}</p>
                    @endif
                </div>
            </div>
        </div>
        
        <table class="invoice-table">
            <thead>
                <tr>
                    <th>Description</th>
                    <th class="text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <strong>Payment Invoice</strong><br>
                        @if($invoice->description)
                            <small>{{ $invoice->description }}</small><br>
                        @endif
                        <small>Reference ID: {{ $invoice->reference_id }}</small>
                    </td>
                    <td class="text-right">
                        <strong>PKR {{ number_format($invoice->amount, 2) }}</strong>
                    </td>
                </tr>
                @if(($invoice->charge && $invoice->charge > 0) || ($invoice->onelink_fee && $invoice->onelink_fee > 0))
                    @if($invoice->charge && $invoice->charge > 0)
                        <tr>
                            <td>
                                <strong>Admin Charge</strong><br>
                                <small>Charges applied based on payment amount slab</small>
                            </td>
                            <td class="text-right">
                                <strong>PKR {{ number_format($invoice->charge, 2) }}</strong>
                            </td>
                        </tr>
                    @endif
                    @if($invoice->onelink_fee && $invoice->onelink_fee > 0)
                        <tr>
                            <td>
                                <strong>Fee Applied to Aggregator - by 1Link</strong><br>
                                <small>Fixed fee based on payment amount slab</small>
                            </td>
                            <td class="text-right">
                                <strong>PKR {{ number_format($invoice->onelink_fee, 2) }}</strong>
                            </td>
                        </tr>
                    @endif
                @endif
                @if($invoice->amount_after_due_date && $invoice->amount_after_due_date > 0)
                    <tr>
                        <td>
                            <strong>Late Payment Fee</strong><br>
                            <small>Additional amount if paid after due date</small>
                        </td>
                        <td class="text-right">
                            <strong>PKR {{ number_format($invoice->amount_after_due_date, 2) }}</strong>
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
        
        <div class="invoice-total">
            <div class="invoice-total-row">
                <div class="invoice-total-label">Subtotal:</div>
                <div class="invoice-total-amount">PKR {{ number_format($invoice->amount, 2) }}</div>
            </div>
            @if($invoice->charge && $invoice->charge > 0)
                <div class="invoice-total-row">
                    <div class="invoice-total-label">Admin Charge:</div>
                    <div class="invoice-total-amount">PKR {{ number_format($invoice->charge, 2) }}</div>
                </div>
            @endif
            @if($invoice->onelink_fee && $invoice->onelink_fee > 0)
                <div class="invoice-total-row">
                    <div class="invoice-total-label">Fee Applied to Aggregator - by 1Link:</div>
                    <div class="invoice-total-amount">PKR {{ number_format($invoice->onelink_fee, 2) }}</div>
                </div>
            @endif
            @if($invoice->amount_after_due_date && $invoice->amount_after_due_date > 0)
                <div class="invoice-total-row">
                    <div class="invoice-total-label">Late Payment Fee:</div>
                    <div class="invoice-total-amount">PKR {{ number_format($invoice->amount_after_due_date, 2) }}</div>
                </div>
            @endif
            <div class="invoice-total-row">
                <div class="invoice-total-label">Total Amount:</div>
                <div class="invoice-total-amount">PKR {{ number_format($invoice->amount + ($invoice->charge ?? 0) + ($invoice->onelink_fee ?? 0) + ($invoice->amount_after_due_date ?? 0), 2) }}</div>
            </div>
        </div>
        
        <div class="invoice-footer">
            <p><strong>{{ config('app.name') }}</strong></p>
            <p>Thank you for your business!</p>
            <p>This is a computer-generated invoice and does not require a signature.</p>
        </div>
    </div>
    
    <script>
        // Auto print when page loads
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
</body>
</html>

