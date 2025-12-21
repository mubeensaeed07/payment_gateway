<!DOCTYPE html>
<html lang="en">
<head>
    <title>Tools - {{ config('app.name') }}</title>
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
        .api-result {
            margin-top: 20px;
            padding: 15px;
            border-radius: 5px;
            display: none;
        }
        .api-result.success {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        .api-result.error {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        .api-result pre {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 3px;
            overflow-x: auto;
            margin-top: 10px;
        }
        .loading {
            display: none;
            text-align: center;
            padding: 20px;
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
                            <h4 class="text-black">Tools</h4>
                            <p class="text-muted">Bill Inquiry and Bill Payment APIs</p>
                        </div>
                    </div>

                    <!-- Bill Inquiry Section -->
                    <div class="col-xl-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Bill Inquiry</h4>
                            </div>
                            <div class="card-body">
                                <form id="inquiryForm">
                                    @csrf
                                    <div class="row">
                                        <div class="col-md-10">
                                            <div class="form-group mb-3">
                                                <label class="form-label">Invoice Number / Customer Number <span class="text-danger">*</span></label>
                                                <input type="text" name="invoice_number" id="inquiry_invoice_number" class="form-control" required>
                                                <small class="text-muted">
                                                    Enter full invoice number (prefix+customer+invoice) to get specific invoice, 
                                                    or just prefix+customer_number to get all invoices for that customer.
                                                    <br>Example: "345410011000" (full) or "34541001" (all invoices)
                                                </small>
                                            </div>
                                        </div>
                                        <div class="col-md-2 d-flex align-items-end">
                                            <button type="submit" class="btn btn-primary w-100">Search</button>
                                        </div>
                                    </div>
                                </form>
                                
                                <div class="loading" id="inquiryLoading">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="sr-only">Loading...</span>
                                    </div>
                                </div>
                                
                                <div class="api-result" id="inquiryResult"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Bill Payment Section -->
                    <div class="col-xl-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Bill Payment</h4>
                            </div>
                            <div class="card-body">
                                <form id="paymentForm">
                                    @csrf
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group mb-3">
                                                <label class="form-label">Invoice Number <span class="text-danger">*</span></label>
                                                <input type="text" name="invoice_number" id="payment_invoice_number" class="form-control" required>
                                                <small class="text-muted">
                                                    Full invoice number (prefix+customer+invoice) or just prefix+customer_number
                                                    <br>Example: "345410011000" (full) or "34541001" (latest unpaid invoice)
                                                </small>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group mb-3">
                                                <label class="form-label">Amount <span class="text-danger">*</span></label>
                                                <input type="number" name="amount" id="payment_amount" step="0.01" min="0.01" class="form-control" required>
                                                <small class="text-muted">Payment amount</small>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group mb-3">
                                                <label class="form-label">Transaction ID</label>
                                                <input type="text" name="transaction_id" id="payment_transaction_id" class="form-control">
                                                <small class="text-muted">Optional</small>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group mb-3">
                                                <label class="form-label">Payment Date</label>
                                                <input type="datetime-local" name="payment_date" id="payment_date" class="form-control">
                                                <small class="text-muted">Optional (defaults to now)</small>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group mb-3">
                                                <label class="form-label">Payment Method</label>
                                                <input type="text" name="payment_method" id="payment_method" class="form-control" placeholder="e.g., easypaisa">
                                                <small class="text-muted">Optional</small>
                                            </div>
                                        </div>
                                        <div class="col-md-1 d-flex align-items-end">
                                            <button type="submit" class="btn btn-success w-100">Pay</button>
                                        </div>
                                    </div>
                                </form>
                                
                                <div class="loading" id="paymentLoading">
                                    <div class="spinner-border text-success" role="status">
                                        <span class="sr-only">Loading...</span>
                                    </div>
                                </div>
                                
                                <div class="api-result" id="paymentResult"></div>
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
    
    <script>
        // Bill Inquiry Form Handler
        $('#inquiryForm').on('submit', function(e) {
            e.preventDefault();
            
            const resultDiv = $('#inquiryResult');
            const loadingDiv = $('#inquiryLoading');
            
            resultDiv.hide().removeClass('success error').html('');
            loadingDiv.show();
            
            $.ajax({
                url: '{{ route("api.bill.inquiry") }}',
                method: 'POST',
                data: {
                    invoice_number: $('#inquiry_invoice_number').val(),
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    loadingDiv.hide();
                    resultDiv.addClass('success').html(
                        '<h5>✓ Success</h5><pre>' + JSON.stringify(response, null, 2) + '</pre>'
                    ).show();
                },
                error: function(xhr) {
                    loadingDiv.hide();
                    const errorResponse = xhr.responseJSON || { message: 'An error occurred' };
                    resultDiv.addClass('error').html(
                        '<h5>✗ Error</h5><pre>' + JSON.stringify(errorResponse, null, 2) + '</pre>'
                    ).show();
                }
            });
        });
        
        // Bill Payment Form Handler
        $('#paymentForm').on('submit', function(e) {
            e.preventDefault();
            
            const resultDiv = $('#paymentResult');
            const loadingDiv = $('#paymentLoading');
            
            resultDiv.hide().removeClass('success error').html('');
            loadingDiv.show();
            
            $.ajax({
                url: '{{ route("api.bill.payment") }}',
                method: 'POST',
                data: {
                    invoice_number: $('#payment_invoice_number').val(),
                    amount: $('#payment_amount').val(),
                    transaction_id: $('#payment_transaction_id').val() || null,
                    payment_date: $('#payment_date').val() || null,
                    payment_method: $('#payment_method').val() || null,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    loadingDiv.hide();
                    resultDiv.addClass('success').html(
                        '<h5>✓ Payment Successful</h5><pre>' + JSON.stringify(response, null, 2) + '</pre>'
                    ).show();
                    
                    // Reset form
                    $('#paymentForm')[0].reset();
                },
                error: function(xhr) {
                    loadingDiv.hide();
                    const errorResponse = xhr.responseJSON || { message: 'An error occurred' };
                    resultDiv.addClass('error').html(
                        '<h5>✗ Payment Failed</h5><pre>' + JSON.stringify(errorResponse, null, 2) + '</pre>'
                    ).show();
                }
            });
        });
    </script>
</body>
</html>

