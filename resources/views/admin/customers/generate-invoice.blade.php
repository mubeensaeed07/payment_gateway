<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Start - Title -->
    <title>Generate Invoice - {{ config('app.name') }}</title>
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
                            <h4 class="text-black">Generate Invoice</h4>
                            <p class="text-muted"><a href="{{ route('admin.customers.index') }}">← Back to Customers</a></p>
                        </div>
                    </div>

                    <div class="col-xl-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Generate Invoice for {{ $customer->name }}</h4>
                            </div>
                            <div class="card-body">
                                @if(session('success'))
                                    <div class="alert alert-success alert-dismissible fade show">
                                        {{ session('success') }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                    </div>
                                @endif

                                @if(session('error'))
                                    <div class="alert alert-danger alert-dismissible fade show">
                                        {{ session('error') }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                    </div>
                                @endif

                                @if($errors->any())
                                    <div class="alert alert-danger alert-dismissible fade show">
                                        <ul class="mb-0">
                                            @foreach($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                    </div>
                                @endif

                                <form action="{{ route('admin.customers.generate-invoice', $customer->id) }}" method="POST" id="invoiceForm">
                                    @csrf
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label class="form-label">Reference ID</label>
                                                <input type="text" class="form-control" value="{{ $customer->reference_id }}" readonly>
                                                <small class="text-muted">Customer's reference ID</small>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label class="form-label">Name</label>
                                                <input type="text" class="form-control" value="{{ $customer->name }}" readonly>
                                                <small class="text-muted">Customer name</small>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label class="form-label">Email</label>
                                                <input type="email" class="form-control" value="{{ $customer->email }}" readonly>
                                                <small class="text-muted">Customer email</small>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label class="form-label">Invoice Amount <span class="text-danger">*</span></label>
                                                <input type="number" name="amount" step="0.01" min="0.01" class="form-control @error('amount') is-invalid @enderror" value="{{ old('amount') }}" required>
                                                @error('amount')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                <small class="text-muted">Enter the invoice amount</small>
                                                
                                                @if($hasUnpaidInvoices)
                                                    <div class="form-check mt-2">
                                                        <input class="form-check-input" type="checkbox" name="block_previous_invoices" id="block_previous_invoices" value="1" {{ old('block_previous_invoices') ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="block_previous_invoices">
                                                            Block previous invoices
                                                        </label>
                                                        <small class="d-block text-muted">This will mark all previous unpaid invoices as blocked</small>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label class="form-label">Invoice Due Date <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <input type="date" name="due_date" id="due_date" class="form-control @error('due_date') is-invalid @enderror" value="{{ old('due_date') }}" min="{{ date('Y-m-d', strtotime('+1 day')) }}" required style="position: relative; z-index: 1;">
                                                    <span class="input-group-text" style="cursor: pointer;" onclick="document.getElementById('due_date').showPicker ? document.getElementById('due_date').showPicker() : document.getElementById('due_date').focus();">
                                                        <i class="fas fa-calendar"></i>
                                                    </span>
                                                </div>
                                                @error('due_date')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                <small class="text-muted">Due date must be after today ({{ date('Y-m-d') }})</small>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label class="form-label">Invoice Expiry Date</label>
                                                <div class="input-group">
                                                    <input type="date" name="expiry_date" id="expiry_date" class="form-control @error('expiry_date') is-invalid @enderror" value="{{ old('expiry_date') }}" min="{{ date('Y-m-d', strtotime('+1 day')) }}" style="position: relative; z-index: 1;">
                                                    <span class="input-group-text" style="cursor: pointer;" onclick="document.getElementById('expiry_date').showPicker ? document.getElementById('expiry_date').showPicker() : document.getElementById('expiry_date').focus();">
                                                        <i class="fas fa-calendar"></i>
                                                    </span>
                                                </div>
                                                @error('expiry_date')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                <small class="text-muted">Optional: Expiry date (must be on or after due date)</small>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label class="form-label">Amount After Due Date</label>
                                                <input type="number" name="amount_after_due_date" step="0.01" min="0" class="form-control @error('amount_after_due_date') is-invalid @enderror" value="{{ old('amount_after_due_date') }}" placeholder="0.00">
                                                @error('amount_after_due_date')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                <small class="text-muted">Optional: Additional amount if paid after due date</small>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label class="form-label">Invoice Description</label>
                                                <textarea name="description" rows="3" class="form-control @error('description') is-invalid @enderror" placeholder="Enter invoice description...">{{ old('description') }}</textarea>
                                                @error('description')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                <small class="text-muted">Optional: Description or notes for this invoice</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary">Generate Invoice</button>
                                        <a href="{{ route('admin.customers.index') }}" class="btn btn-secondary">Cancel</a>
                                    </div>
                                </form>
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
    
    <style>
        /* Prevent date picker from interfering with other fields */
        input[type="date"] {
            position: relative;
            z-index: 1;
            pointer-events: auto;
        }
        
        input[type="date"]::-webkit-calendar-picker-indicator {
            cursor: pointer;
            z-index: 2;
            pointer-events: auto;
        }
        
        /* Ensure form fields don't trigger date picker */
        .form-control:not([type="date"]):focus,
        textarea:focus {
            z-index: 10;
            position: relative;
            pointer-events: auto;
        }
        
        /* Prevent date picker from opening on other elements */
        .form-group:not(:has(input[type="date"])) {
            pointer-events: auto;
        }
        
        /* Ensure input group works properly */
        .input-group {
            position: relative;
        }
        
        .input-group-text {
            pointer-events: auto;
        }
    </style>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const dueDateInput = document.getElementById('due_date');
            const expiryDateInput = document.getElementById('expiry_date');
            const invoiceForm = document.getElementById('invoiceForm');
            
            // Prevent date picker from opening when clicking other fields
            document.querySelectorAll('.form-control:not([type="date"]), textarea, button, a').forEach(function(field) {
                field.addEventListener('mousedown', function(e) {
                    // Close any open date pickers immediately
                    if (dueDateInput && document.activeElement === dueDateInput) {
                        setTimeout(function() {
                            dueDateInput.blur();
                        }, 0);
                    }
                    if (expiryDateInput && document.activeElement === expiryDateInput) {
                        setTimeout(function() {
                            expiryDateInput.blur();
                        }, 0);
                    }
                });
                
                field.addEventListener('focus', function(e) {
                    // Close any open date pickers
                    if (dueDateInput && document.activeElement !== dueDateInput) {
                        dueDateInput.blur();
                    }
                    if (expiryDateInput && document.activeElement !== expiryDateInput) {
                        expiryDateInput.blur();
                    }
                });
                
                field.addEventListener('click', function(e) {
                    e.stopPropagation();
                    // Close any open date pickers
                    if (dueDateInput) {
                        dueDateInput.blur();
                    }
                    if (expiryDateInput) {
                        expiryDateInput.blur();
                    }
                });
            });
            
            // Only allow date picker to open when clicking the date input itself
            if (dueDateInput) {
                dueDateInput.addEventListener('focus', function(e) {
                    e.stopPropagation();
                });
                
                dueDateInput.addEventListener('click', function(e) {
                    e.stopPropagation();
                });
            }
            
            if (expiryDateInput) {
                expiryDateInput.addEventListener('focus', function(e) {
                    e.stopPropagation();
                });
                
                expiryDateInput.addEventListener('click', function(e) {
                    e.stopPropagation();
                });
            }
            
            // Update expiry date minimum when due date changes
            if (dueDateInput && expiryDateInput) {
                dueDateInput.addEventListener('change', function() {
                    if (this.value) {
                        expiryDateInput.min = this.value;
                        // If expiry date is before new due date, clear it
                        if (expiryDateInput.value && expiryDateInput.value < this.value) {
                            expiryDateInput.value = '';
                        }
                    }
                });
            }
            
            // Close date picker when clicking outside
            document.addEventListener('click', function(e) {
                if (!dueDateInput.contains(e.target) && !expiryDateInput.contains(e.target)) {
                    if (document.activeElement === dueDateInput) {
                        dueDateInput.blur();
                    }
                    if (document.activeElement === expiryDateInput) {
                        expiryDateInput.blur();
                    }
                }
            });
            
            // Client-side validation for due date
            if (invoiceForm) {
                invoiceForm.addEventListener('submit', function(e) {
                    const dueDate = dueDateInput.value;
                    const today = new Date().toISOString().split('T')[0];
                    
                    if (dueDate <= today) {
                        e.preventDefault();
                        alert('Due date must be after today\'s date. Please select a future date.');
                        dueDateInput.focus();
                        return false;
                    }
                    
                    // Validate expiry date is after or equal to due date
                    const expiryDate = expiryDateInput.value;
                    if (expiryDate && expiryDate < dueDate) {
                        e.preventDefault();
                        alert('Expiry date must be on or after the due date.');
                        expiryDateInput.focus();
                        return false;
                    }
                });
            }
        });
    </script>
</body>
</html>

