<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Start - Title -->
    <title>Invoice History - {{ $customer->name }} - {{ config('app.name') }}</title>
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
                            <h4 class="text-black">Invoice History</h4>
                            <p class="text-muted">
                                <a href="{{ route('admin.customers.index') }}">← Back to Customers</a> | 
                                Customer: <strong>{{ $customer->name }}</strong> ({{ $customer->user_number }})
                            </p>
                        </div>
                    </div>

                    <!-- Customer Info Card -->
                    <div class="col-xl-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Customer Information</h4>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <p><strong>Name:</strong> {{ $customer->name }}</p>
                                    </div>
                                    <div class="col-md-3">
                                        <p><strong>Email:</strong> {{ $customer->email }}</p>
                                    </div>
                                    <div class="col-md-3">
                                        <p><strong>User Number:</strong> {{ $customer->user_number }}</p>
                                    </div>
                                    <div class="col-md-3">
                                        <p><strong>Current Balance:</strong> <span class="text-primary">PKR {{ number_format($customer->balance, 2) }}</span></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Invoice History Table -->
                    <div class="col-xl-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">All Invoices ({{ $invoices->count() }})</h4>
                            </div>
                            <div class="card-body">
                                @if($invoices->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-responsive-md">
                                            <thead>
                                                <tr>
                                                    <th>Invoice #</th>
                                                    <th>Amount</th>
                                                    <th>Due Date</th>
                                                    <th>Expiry Date</th>
                                                    <th>Status</th>
                                                    <th>Paid Date</th>
                                                    <th>Created Date</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($invoices as $invoice)
                                                    <tr>
                                                        <td><strong>#{{ $invoice->invoice_number }}</strong></td>
                                                        <td>PKR {{ number_format($invoice->amount, 2) }}</td>
                                                        <td>
                                                            @if($invoice->due_date)
                                                                {{ $invoice->due_date->format('Y-m-d') }}
                                                            @else
                                                                <span class="text-muted">-</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if($invoice->expiry_date)
                                                                {{ $invoice->expiry_date->format('Y-m-d') }}
                                                            @else
                                                                <span class="text-muted">-</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if($invoice->status === 'paid')
                                                                <span class="badge bg-success">Paid</span>
                                                            @elseif($invoice->status === 'pending')
                                                                <span class="badge bg-warning">Pending</span>
                                                            @elseif($invoice->status === 'blocked')
                                                                <span class="badge bg-secondary">Blocked</span>
                                                            @else
                                                                <span class="badge bg-danger">{{ ucfirst($invoice->status) }}</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if($invoice->paid_at)
                                                                {{ $invoice->paid_at->format('Y-m-d H:i') }}
                                                            @else
                                                                <span class="text-muted">-</span>
                                                            @endif
                                                        </td>
                                                        <td>{{ $invoice->created_at->format('Y-m-d H:i') }}</td>
                                                        <td>
                                                            <a href="{{ route('admin.invoices.show', $invoice->id) }}" class="btn btn-primary btn-sm" title="View Invoice">
                                                                <i class="fas fa-eye"></i> View
                                                            </a>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="alert alert-info">
                                        <p class="mb-0">No invoices found for this customer.</p>
                                    </div>
                                @endif
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
