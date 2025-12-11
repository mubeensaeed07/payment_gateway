<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Start - Title -->
    <title>Login - {{ config('app.name') }}</title>
    <!-- End - Title -->

    <!-- Start - Meta -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- End - Meta -->

    <!-- Start - Favicon icon -->
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/images/favicon.avif') }}">
    <!-- End - Favicon icon -->

    <!-- Start - Style CSS -->
    <link class="main-plugins" href="{{ asset('assets/css/plugins.css') }}" rel="stylesheet">
    <link class="main-css" href="{{ asset('assets/css/style.css') }}" rel="stylesheet">
    <!-- End - Style CSS -->

    <!-- Google reCAPTCHA - DISABLED -->
    <!-- <script src="https://www.google.com/recaptcha/api.js" async defer></script> -->
</head>

<body>
    <!-- Start - Authentication Wrapper -->
    <div class="auth-wrapper">
        <div class="container">
            <div class="row justify-content-center vh-100">
                <div class="col-lg-5 col-md-6 mx-auto align-self-center bg-white">
                    <div class="auth-form">
                        <div class="card-body">
                            <div class="text-center mb-3">
                                <a href="{{ url('/') }}"><img class="logo-auth" src="{{ asset('assets/images/logo-full.avif') }}" width="200" alt=""></a>
                            </div>
                            <h4 class="text-center mb-4">Sign in to your account</h4>

                            @if(session('error'))
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    {{ session('error') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            @endif

                            @if(session('success'))
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    {{ session('success') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            @endif

                            <form id="loginForm" action="{{ route('google.redirect') }}" method="GET">
                                <!-- reCAPTCHA - DISABLED -->
                                {{-- @if(config('services.recaptcha.site_key'))
                                <div class="form-group mb-4">
                                    <div class="g-recaptcha" data-sitekey="{{ config('services.recaptcha.site_key') }}"></div>
                                    @error('g-recaptcha-response')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                @endif --}}

                                <div class="text-center">
                                    <button type="submit" class="btn btn-primary btn-block w-100 fs-16" id="loginBtn">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="me-2">
                                            <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                                            <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                                            <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                                            <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                                        </svg>
                                        Sign in with Google
                                    </button>
                                </div>
                            </form>

                            <div class="text-center mt-4">
                                <p class="text-muted small">Only authorized users can access this system</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End - Authentication Wrapper -->

    <!-- Start - Page Scripts -->
    <script src="{{ asset('assets/vendor/jquery/dist/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/bootstrap/dist/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/js/custom.js') }}"></script>

    <script>
        // reCAPTCHA validation - DISABLED
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            // reCAPTCHA check disabled
            {{-- @if(config('services.recaptcha.site_key'))
            const recaptchaResponse = grecaptcha.getResponse();
            if (!recaptchaResponse) {
                e.preventDefault();
                alert('Please complete the reCAPTCHA verification.');
                return false;
            }
            @endif --}}
        });
    </script>
</body>
</html>
