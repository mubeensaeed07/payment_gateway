<!DOCTYPE html>
<html lang="en">
<head>
    <title>All Invoices - {{ config('app.name') }}</title>
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
    <style>
        .summary-card {
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .summary-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        .summary-card.active {
            border: 2px solid #007bff;
        }
    </style>
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
                            <h4 class="text-black">All Invoices</h4>
                        </div>
                    </div>

                    <!-- Summary Cards -->
                    <div class="col-xl-6">
                        <div class="card summary-card {{ request('status') == 'pending' ? 'active' : '' }}" onclick="window.location.href='{{ route('admin.reports.all-invoices', ['status' => 'pending']) }}'">
                            <div class="card-body text-center">
                                <h3 class="text-warning">{{ $totalNonPaid }}</h3>
                                <p class="mb-0">Non Paid Invoices</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-6">
                        <div class="card summary-card {{ request('status') == 'paid' ? 'active' : '' }}" onclick="window.location.href='{{ route('admin.reports.all-invoices', ['status' => 'paid']) }}'">
                            <div class="card-body text-center">
                                <h3 class="text-success">{{ $totalPaid }}</h3>
                                <p class="mb-0">Paid Invoices</p>
                            </div>
                        </div>
                    </div>

                    <!-- Invoices Table -->
                    <div class="col-xl-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4 class="card-title">
                                    @if(request('status') == 'pending')
                                        Non Paid Invoices
                                    @elseif(request('status') == 'paid')
                                        Paid Invoices
                                    @else
                                        All Invoices
                                    @endif
                                    ({{ $invoices->total() }})
                                </h4>
                                @if(request('status'))
                                    <a href="{{ route('admin.reports.all-invoices') }}" class="btn btn-secondary btn-sm">Show All</a>
                                @endif
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
                                                    <th>Status</th>
                                                    <th>Bank</th>
                                                    <th>Due Date</th>
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
                                                            @if($invoice->bank)
                                                                {{ $invoice->bank }}
                                                            @else
                                                                <span class="text-muted">-</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if($invoice->due_date)
                                                                {{ $invoice->due_date->format('Y-m-d') }}
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
                                        <p class="mb-0">No invoices found.</p>
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

