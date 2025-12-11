<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to {{ config('app.name') }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: #f4f4f4; padding: 20px; border-radius: 5px; margin-bottom: 20px;">
        <h2 style="color: #2130B8; margin-top: 0;">Welcome to {{ config('app.name') }}!</h2>
    </div>
    
    <p>Hello {{ $customer->name }},</p>
    
    <p>Your account has been created successfully. Here are your account details:</p>
    
    <div style="background: #f9f9f9; padding: 15px; border-radius: 5px; margin: 20px 0;">
        <p><strong>User Number:</strong> {{ $customer->user_number }}</p>
        <p><strong>Reference ID:</strong> {{ $customer->reference_id }}</p>
        <p><strong>Email:</strong> {{ $customer->email }}</p>
        <p><strong>Balance:</strong> PKR {{ number_format($customer->balance, 2) }}</p>
    </div>
    
    <p>You can now access your account and manage your invoices.</p>
    
    <p style="margin-top: 30px; color: #666; font-size: 12px;">
        If you have any questions, please contact your administrator.
    </p>
    
    <hr style="border: none; border-top: 1px solid #eee; margin: 30px 0;">
    
    <p style="color: #666; font-size: 12px; text-align: center;">
        © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
    </p>
</body>
</html>

