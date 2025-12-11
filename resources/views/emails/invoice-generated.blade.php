<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Invoice - {{ config('app.name') }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: #f4f4f4; padding: 20px; border-radius: 5px; margin-bottom: 20px;">
        <h2 style="color: #2130B8; margin-top: 0;">New Invoice Generated</h2>
    </div>
    
    <p>Hello {{ $invoice->customer->name }},</p>
    
    <p>A new invoice has been generated for your account. Please find the details below:</p>
    
    <div style="background: #f9f9f9; padding: 15px; border-radius: 5px; margin: 20px 0;">
        <p><strong>Invoice Number:</strong> {{ $invoice->invoice_number }}</p>
        <p><strong>Reference ID:</strong> {{ $invoice->reference_id }}</p>
        <p><strong>Customer Name:</strong> {{ $invoice->customer->name }}</p>
        <p><strong>Email:</strong> {{ $invoice->customer->email }}</p>
        <p><strong>Invoice Amount:</strong> PKR {{ number_format($invoice->amount, 2) }}</p>
        <p><strong>Status:</strong> <span style="text-transform: capitalize;">{{ $invoice->status }}</span></p>
        <p><strong>Date:</strong> {{ $invoice->created_at->format('F d, Y') }}</p>
    </div>
    
    <p>Please review the invoice and contact us if you have any questions.</p>
    
    <p style="margin-top: 30px; color: #666; font-size: 12px;">
        If you have any questions about this invoice, please contact your administrator.
    </p>
    
    <hr style="border: none; border-top: 1px solid #eee; margin: 30px 0;">
    
    <p style="color: #666; font-size: 12px; text-align: center;">
        © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
    </p>
</body>
</html>

