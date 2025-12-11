<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Start - Title -->
    <title>Reseller Dashboard - {{ config('app.name') }}</title>
    <!-- End - Title -->

    <!-- Start - Meta -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <!-- End - Meta -->

    <!-- Start - Favicon icon -->
    <link rel="icon" type="image/png" sizes="16x16" href="/assets/images/favicon.avif">
    <!-- End - Favicon icon -->

    <link rel="stylesheet" href="/assets/vendor/chartist/css/chartist.min.css">

    <!-- Start - Extra Css -->
    <link href="/assets/vendor/metismenu/dist/metisMenu.min.css" rel="stylesheet">
    <link href="/assets/vendor/bootstrap-select/dist/css/bootstrap-select.min.css" rel="stylesheet">
    <link href="/assets/vendor/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" rel="stylesheet">
    <link href="/assets/vendor/bootstrap-datepicker/css/bootstrap-datepicker3.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/vendor/chartist/css/chartist.min.css">
    <!-- End - Extra Css -->

    <!-- Start - Switcher CSS -->
    <link href="/assets/css/switcher.css" rel="stylesheet">
    <!-- End - Switcher CSS -->

    <!-- Start - Style CSS -->
    <link class="main-plugins" href="/assets/css/plugins.css" rel="stylesheet">
    <link class="main-css" href="/assets/css/style.css" rel="stylesheet">
    <!-- End - Style CSS -->
</head>
<body>

    <!-- Start - Preloader-->
    <div id="preloader">
        <div class="sk-three-bounce">
            <div class="sk-child sk-bounce1"></div>
            <div class="sk-child sk-bounce2"></div>
            <div class="sk-child sk-bounce3"></div>
        </div>
    </div>
    <!-- End - Preloader -->

    <!-- Start - Main wrapper -->
    <div id="main-wrapper">
        @include('includes.header')
        @include('includes.sidebar')

        <!-- Start - Content Body -->
        <div class="content-body">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-xl-12">
                        <div class="page-titles">
                            <h4 class="text-black">Reseller Dashboard</h4>
                            <p class="text-muted">Welcome, {{ auth()->user()->name }} (Reseller)</p>
                        </div>
                    </div>
                    <div class="col-xl-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Reseller Panel</h4>
                            </div>
                            <div class="card-body">
                                <p>Welcome to the Reseller Dashboard. This is your control panel.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End - Content Body -->

        <!-- Start - Footer -->
        <div class="footer">
            <div class="copyright">
                <p>Copyright © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
            </div>
        </div>
        <!-- End - Footer -->
    </div>
    <!-- End - Main wrapper -->

    <!-- Start - Page Scripts -->
    <script src="/assets/vendor/jquery/dist/jquery.min.js"></script>
    <script src="/assets/vendor/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/vendor/bootstrap-select/dist/js/bootstrap-select.min.js"></script>
    <script src="/assets/vendor/metismenu/dist/metisMenu.min.js"></script>
    <script src="/assets/vendor/@yaireo/tagify/dist/tagify.js"></script>
    <script src="/assets/vendor/chart-js/chart.bundle.min.js"></script>
    <script src="/assets/vendor/bootstrap-datetimepicker/js/moment.js"></script>
    <script src="/assets/vendor/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
    <script src="/assets/vendor/apexcharts/dist/apexcharts.min.js"></script>
    <script src="/assets/vendor/peity/jquery.peity.min.js"></script>
    
    <!-- Script For Multiple Languages -->
    <script src="/assets/vendor/i18n/i18n.js"></script>
    <script src="/assets/js/translator.js"></script>
    
    <!-- Script For Custom JS -->
    <script src="/assets/js/deznav-init.js"></script>
    <script src="/assets/js/custom.js"></script>
</body>
</html>

