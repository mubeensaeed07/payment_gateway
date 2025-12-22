<!DOCTYPE html>
<html lang="en">
<head>
    <title>API Payments - {{ config('app.name') }}</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" sizes="16x16" href="/assets/images/favicon.avif">
    <link rel="stylesheet" href="/assets/vendor/chartist/css/chartist.min.css">
    <link href="/assets/vendor/metismenu/dist/metisMenu.min.css" rel="stylesheet">
    <link href="/assets/vendor/bootstrap-select/dist/css/bootstrap-select.min.css" rel="stylesheet">
    <link href="/assets/vendor/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" rel="stylesheet">
    <link href="/assets/vendor/bootstrap-datepicker/css/bootstrap-datepicker3.min.css" rel="stylesheet">
    <link href="/assets/css/switcher.css" rel="stylesheet">
    <link class="main-plugins" href="/assets/css/plugins.css" rel="stylesheet">
    <link class="main-css" href="/assets/css/style.css" rel="stylesheet">
</head>
<body>
    <div id="preloader">
        <div class="sk-three-bounce">
            <div class="sk-child sk-bounce1"></div>
            <div class="sk-child sk-bounce2"></div>
            <div class="sk-child sk-bounce3"></div>
        </div>
    </div>

    <div id="main-wrapper">
        @include('includes.header')
        @include('includes.sidebar')

        <div class="content-body">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-xl-12">
                        <div class="page-titles">
                            <h4 class="text-black">API Payments</h4>
                        </div>
                    </div>

                    <!-- Summary Cards -->
                    <div class="col-xl-12">
                        <div class="row">
                            <div class="col-xl-6 col-lg-6 col-sm-6">
                                <div class="widget-stat card">
                                    <div class="card-body p-4">
                                        <div class="media ai-icon">
                                            <span class="me-3 bgl-primary text-primary">
                                                <i class="flaticon-381-user-8"></i>
                                            </span>
                                            <div class="media-body">
                                                <p class="mb-1">Total API Payments</p>
                                                <h4 class="mb-0">{{ $totalApiPayments }}</h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-6 col-lg-6 col-sm-6">
                                <div class="widget-stat card">
                                    <div class="card-body p-4">
                                        <div class="media ai-icon">
                                            <span class="me-3 bgl-success text-success">
                                                <i class="flaticon-381-diamond"></i>
                                            </span>
                                            <div class="media-body">
                                                <p class="mb-1">Total Amount</p>
                                                <h4 class="mb-0">PKR {{ number_format($totalApiAmount, 2) }}</h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filter Card -->
                    <div class="col-xl-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Filter by Paid Date</h4>
                            </div>
                            <div class="card-body">
                                <form method="GET" action="{{ route('admin.reports.api-payments') }}" class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">From Date</label>
                                        <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">To Date</label>
                                        <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                                    </div>
                                    <div class="col-md-4 d-flex align-items-end">
                                        <button type="submit" class="btn btn-primary me-2">Filter</button>
                                        <a href="{{ route('admin.reports.api-payments') }}" class="btn btn-secondary">Clear</a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Invoices Table -->
                    <div class="col-xl-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Payments Made Via API ({{ $invoices->total() }})</h4>
                            </div>
                            <div class="card-body">
                                @if($invoices->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-responsive-md">
                                            <thead>
                                                <tr>
                                                    <th>Invoice #</th>
                                                    <th>Customer</th>
                                                    <th>Customer Number</th>
                                                    <th>Amount</th>
                                                    <th>Charge</th>
                                                    <th>Total</th>
                                                    <th>Bank</th>
                                                    <th>Paid Date</th>
                                                    <th>Created Date</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($invoices as $invoice)
                                                    <tr>
                                                        <td><strong>#{{ $invoice->invoice_number }}</strong></td>
                                                        <td>{{ $invoice->customer->name }}</td>
                                                        <td>{{ $invoice->customer->user_number }}</td>
                                                        <td>PKR {{ number_format($invoice->amount, 2) }}</td>
                                                        <td>PKR {{ number_format($invoice->charge ?? 0, 2) }}</td>
                                                        <td><strong>PKR {{ number_format($invoice->amount + ($invoice->charge ?? 0), 2) }}</strong></td>
                                                        <td>
                                                            @if($invoice->bank)
                                                                <span class="badge bg-info">{{ $invoice->bank }}</span>
                                                            @else
                                                                <span class="text-muted">-</span>
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
                                                            <a href="{{ route('admin.invoices.show', $invoice->id) }}" class="btn btn-primary btn-sm">
                                                                <i class="fas fa-eye"></i> View
                                                            </a>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    {{ $invoices->links() }}
                                @else
                                    <div class="alert alert-info">
                                        <p class="mb-0">No API payments found.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="footer">
            <div class="copyright">
                <p>Copyright © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
            </div>
        </div>
    </div>

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
    <script src="/assets/vendor/i18n/i18n.js"></script>
    <script src="/assets/js/translator.js"></script>
    <script src="/assets/js/deznav-init.js"></script>
    <script src="/assets/js/custom.js"></script>
</body>
</html>

