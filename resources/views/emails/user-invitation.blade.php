<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invitation to {{ config('app.name') }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: #f4f4f4; padding: 20px; border-radius: 5px; margin-bottom: 20px;">
        <h2 style="color: #2130B8; margin-top: 0;">Welcome to {{ config('app.name') }}!</h2>
    </div>
    
    <p>Hello {{ $user->name }},</p>
    
    <p>You have been invited to join our system as a <strong>{{ ucfirst($user->role) }}</strong>.</p>
    
    <p>To get started, please click the button below to sign in with your Google account:</p>
    
    <div style="text-align: center; margin: 30px 0;">
        <a href="{{ $loginUrl }}" 
           style="background-color: #2130B8; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block; font-weight: bold;">
            Sign In with Google
        </a>
    </div>
    
    <p>Or copy and paste this link into your browser:</p>
    <p style="word-break: break-all; color: #2130B8;">{{ $loginUrl }}</p>
    
    <p style="margin-top: 30px; color: #666; font-size: 12px;">
        If you did not expect this invitation, please ignore this email.
    </p>
    
    <hr style="border: none; border-top: 1px solid #eee; margin: 30px 0;">
    
    <p style="color: #666; font-size: 12px; text-align: center;">
        © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
    </p>
</body>
</html>

