<!DOCTYPE html>
<html lang="en">
<head>
    <title>External Provider Logs - {{ config('app.name') }}</title>
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
                            <h4 class="text-black">External Provider Logs (02 Hits)</h4>
                        </div>
                    </div>

                    <!-- Filter Card -->
                    <div class="col-xl-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Search Logs</h4>
                            </div>
                            <div class="card-body">
                                <form method="GET" action="{{ route('admin.logs.index') }}" class="row g-3">
                                    <div class="col-md-3">
                                        <label class="form-label">From Date</label>
                                        <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">To Date</label>
                                        <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Customer Name</label>
                                        <input type="text" name="customer_name" class="form-control" value="{{ request('customer_name') }}" placeholder="Enter customer name">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Invoice Number / Customer Number</label>
                                        <input type="text" name="invoice_number" class="form-control" value="{{ request('invoice_number') }}" placeholder="Full invoice or prefix+customer">
                                    </div>
                                    <div class="col-md-12 d-flex align-items-end">
                                        <button type="submit" class="btn btn-primary me-2">Search</button>
                                        <a href="{{ route('admin.logs.index') }}" class="btn btn-secondary">Clear</a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Logs Table -->
                    <div class="col-xl-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Logs ({{ $logs->total() }})</h4>
                            </div>
                            <div class="card-body">
                                @if($logs->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-responsive-md">
                                            <thead>
                                                <tr>
                                                    <th>Date/Time</th>
                                                    <th>API Type</th>
                                                    <th>Invoice Number</th>
                                                    <th>Customer Name</th>
                                                    <th>Customer Number</th>
                                                    <th>Status</th>
                                                    <th>Response Status</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($logs as $log)
                                                    <tr>
                                                        <td>{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                                                        <td>
                                                            @php
                                                                $apiType = $log->api_type;
                                                                if (!empty($apiType)) {
                                                                    $badgeClass = ($apiType === 'inquiry') ? 'bg-info' : 'bg-success';
                                                                    echo '<span class="badge ' . $badgeClass . '">' . strtoupper($apiType) . '</span>';
                                                                } else {
                                                                    echo '<span class="text-muted">-</span>';
                                                                }
                                                            @endphp
                                                        </td>
                                                        <td><strong>{{ $log->invoice_number ?? '-' }}</strong></td>
                                                        <td>{{ !empty($log->customer_name) ? trim($log->customer_name) : '-' }}</td>
                                                        <td>{{ !empty($log->customer_number) ? trim($log->customer_number) : '-' }}</td>
                                                        <td>
                                                            @php
                                                                $isSuccessful = $log->is_successful;
                                                                if ($isSuccessful === true || $isSuccessful === 1 || $isSuccessful === '1') {
                                                                    echo '<span class="badge bg-success">Success</span>';
                                                                } elseif ($isSuccessful === false || $isSuccessful === 0 || $isSuccessful === '0') {
                                                                    echo '<span class="badge bg-danger">Failed</span>';
                                                                } else {
                                                                    echo '<span class="text-muted">-</span>';
                                                                }
                                                            @endphp
                                                        </td>
                                                        <td>
                                                            @php
                                                                $responseStatus = $log->response_status;
                                                                if (isset($responseStatus) && $responseStatus !== null && $responseStatus !== '') {
                                                                    $statusInt = (int)$responseStatus;
                                                                    $badgeClass = ($statusInt >= 200 && $statusInt < 300) ? 'bg-success' : 'bg-danger';
                                                                    echo '<span class="badge ' . $badgeClass . '">' . $statusInt . '</span>';
                                                                } else {
                                                                    echo '<span class="text-muted">-</span>';
                                                                }
                                                            @endphp
                                                        </td>
                                                        <td>
                                                            <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#logModal{{ $log->id }}">
                                                                <i class="fas fa-eye"></i> View Details
                                                            </button>
                                                        </td>
                                                    </tr>

                                                    <!-- Modal for Log Details -->
                                                    <div class="modal fade" id="logModal{{ $log->id }}" tabindex="-1" aria-labelledby="logModalLabel{{ $log->id }}" aria-hidden="true">
                                                        <div class="modal-dialog modal-lg">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title" id="logModalLabel{{ $log->id }}">Log Details</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <div class="row mb-3">
                                                                        <div class="col-md-6">
                                                                            <strong>Date/Time:</strong> {{ $log->created_at->format('Y-m-d H:i:s') }}
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                            <strong>API Type:</strong> 
                                                                            @php
                                                                                $apiType = $log->api_type;
                                                                                if (!empty($apiType)) {
                                                                                    $badgeClass = ($apiType === 'inquiry') ? 'bg-info' : 'bg-success';
                                                                                    echo '<span class="badge ' . $badgeClass . '">' . strtoupper($apiType) . '</span>';
                                                                                } else {
                                                                                    echo '<span class="text-muted">-</span>';
                                                                                }
                                                                            @endphp
                                                                        </div>
                                                                    </div>
                                                                    <div class="row mb-3">
                                                                        <div class="col-md-6">
                                                                            <strong>Invoice Number:</strong> {{ $log->invoice_number }}
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                            <strong>Customer:</strong> {{ !empty($log->customer_name) ? trim($log->customer_name) : '-' }} ({{ !empty($log->customer_number) ? trim($log->customer_number) : '-' }})
                                                                        </div>
                                                                    </div>
                                                                    <div class="row mb-3">
                                                                        <div class="col-md-6">
                                                                            <strong>Status:</strong> 
                                                                            @php
                                                                                $isSuccessful = $log->is_successful;
                                                                                if ($isSuccessful === true || $isSuccessful === 1 || $isSuccessful === '1') {
                                                                                    echo '<span class="badge bg-success">Success</span>';
                                                                                } elseif ($isSuccessful === false || $isSuccessful === 0 || $isSuccessful === '0') {
                                                                                    echo '<span class="badge bg-danger">Failed</span>';
                                                                                } else {
                                                                                    echo '<span class="text-muted">-</span>';
                                                                                }
                                                                            @endphp
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                            <strong>Response Status:</strong> 
                                                                            @php
                                                                                $responseStatus = $log->response_status;
                                                                                if (isset($responseStatus) && $responseStatus !== null && $responseStatus !== '') {
                                                                                    $statusInt = (int)$responseStatus;
                                                                                    $badgeClass = ($statusInt >= 200 && $statusInt < 300) ? 'bg-success' : 'bg-danger';
                                                                                    echo '<span class="badge ' . $badgeClass . '">' . $statusInt . '</span>';
                                                                                } else {
                                                                                    echo '<span class="text-muted">-</span>';
                                                                                }
                                                                            @endphp
                                                                        </div>
                                                                    </div>
                                                                    @if($log->external_provider_url)
                                                                        <div class="row mb-3">
                                                                            <div class="col-md-12">
                                                                                <strong>External Provider URL:</strong> 
                                                                                <code>{{ $log->external_provider_url }}</code>
                                                                            </div>
                                                                        </div>
                                                                    @endif
                                                                    @if($log->error_message)
                                                                        <div class="row mb-3">
                                                                            <div class="col-md-12">
                                                                                <strong>Error Message:</strong> 
                                                                                <div class="alert alert-danger mt-2">
                                                                                    {{ $log->error_message }}
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    @endif
                                                                    <div class="row mb-3">
                                                                        <div class="col-md-12">
                                                                            <strong>Request Data:</strong>
                                                                            <pre class="bg-light p-3 mt-2" style="max-height: 200px; overflow-y: auto;"><code>{{ json_encode($log->request_data, JSON_PRETTY_PRINT) }}</code></pre>
                                                                        </div>
                                                                    </div>
                                                                    @if($log->response_data)
                                                                        <div class="row mb-3">
                                                                            <div class="col-md-12">
                                                                                <strong>Response Data:</strong>
                                                                                <pre class="bg-light p-3 mt-2" style="max-height: 200px; overflow-y: auto;"><code>{{ json_encode($log->response_data, JSON_PRETTY_PRINT) }}</code></pre>
                                                                            </div>
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    {{ $logs->links() }}
                                @else
                                    <div class="alert alert-info">
                                        <p class="mb-0">No logs found.</p>
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

