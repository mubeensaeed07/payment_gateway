# Payment Gateway Setup Instructions

## Installation Steps

### 1. Install Dependencies

```bash
composer require laravel/socialite
composer install
```

### 2. Environment Configuration

Add these to your `.env` file:

```env
# Google OAuth
GOOGLE_CLIENT_ID=your_google_client_id
GOOGLE_CLIENT_SECRET=your_google_client_secret
GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback

# reCAPTCHA
RECAPTCHA_SITE_KEY=your_recaptcha_site_key
RECAPTCHA_SECRET_KEY=your_recaptcha_secret_key

# Mail Configuration (for sending invitations)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=your_app_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your_email@gmail.com
MAIL_FROM_NAME="${APP_NAME}"
```

### 3. Run Migrations and Seeders

```bash
php artisan migrate:fresh --seed
```

This will:
- Create the users table with roles
- Create the superadmin user (saeedmubeen20@gmail.com)

### 4. Google OAuth Setup

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select existing
3. Enable Google+ API
4. Go to Credentials → Create OAuth 2.0 Client ID
5. Add authorized redirect URI: `http://localhost:8000/auth/google/callback`
6. Copy Client ID and Secret to `.env`

### 5. reCAPTCHA Setup

1. Go to [Google reCAPTCHA](https://www.google.com/recaptcha/admin)
2. Register a new site (reCAPTCHA v2)
3. Copy Site Key and Secret Key to `.env`

### 6. Start the Server

```bash
php artisan serve
```

## System Overview

### Roles:
- **Superadmin**: Only 1 user (saeedmubeen20@gmail.com)
  - Can create admins and resellers
  - Full system access
  
- **Admin**: Created by superadmin
  - Can manage resellers (if needed)
  
- **Reseller**: Created by superadmin
  - Basic access

### Authentication:
- Only Google OAuth login (no password)
- reCAPTCHA on login page
- No signup page - users created by superadmin
- Invitation emails sent when user is created

### Workflow:
1. Superadmin logs in with Google
2. Superadmin creates admin/reseller with email
3. System sends invitation email
4. User clicks link and signs in with Google
5. User gets access based on role

## Routes

- `/login` - Login page (Google OAuth + reCAPTCHA)
- `/superadmin/dashboard` - Superadmin dashboard
- `/superadmin/users` - Manage users
- `/admin/dashboard` - Admin dashboard
- `/reseller/dashboard` - Reseller dashboard

## Next Steps

1. Configure Google OAuth credentials
2. Configure reCAPTCHA keys
3. Set up email (SMTP)
4. Run migrations
5. Test login with superadmin account

