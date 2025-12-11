<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Start - Title -->
    <title>Manage Customers - {{ config('app.name') }}</title>
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
                            <h4 class="text-black">Manage Customers</h4>
                        </div>
                    </div>

                    <!-- Success/Error Messages -->
                    @if(session('success'))
                        <div class="col-xl-12">
                            <div class="alert alert-success alert-dismissible fade show">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="col-xl-12">
                            <div class="alert alert-danger alert-dismissible fade show">
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        </div>
                    @endif

                    <!-- Customers List -->
                    <div class="col-xl-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4 class="card-title">All Customers</h4>
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createCustomerModal">
                                    <i class="fas fa-plus"></i> Create New Customer
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-responsive-md">
                                        <thead>
                                            <tr>
                                                <th>User Number</th>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Reference ID</th>
                                                <th>Balance</th>
                                                <th>Status</th>
                                                <th>Next Date</th>
                                                <th>Created</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($customers as $customer)
                                                @php
                                                    $latestInvoice = $customer->invoices->first();
                                                @endphp
                                                <tr>
                                                    <td><strong>{{ $customer->user_number }}</strong></td>
                                                    <td>{{ $customer->name }}</td>
                                                    <td>{{ $customer->email }}</td>
                                                    <td>{{ $customer->reference_id }}</td>
                                                    <td>PKR {{ number_format($customer->balance, 2) }}</td>
                                                    <td>
                                                        @if($latestInvoice)
                                                            @if($latestInvoice->status === 'paid')
                                                                <span class="badge bg-success">Paid</span>
                                                            @elseif($latestInvoice->status === 'pending')
                                                                <span class="badge bg-warning">Pending</span>
                                                            @elseif($latestInvoice->status === 'blocked')
                                                                <span class="badge bg-secondary">Blocked</span>
                                                            @else
                                                                <span class="badge bg-danger">{{ ucfirst($latestInvoice->status) }}</span>
                                                            @endif
                                                        @else
                                                            <span class="badge bg-secondary">No Invoice</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <span class="text-muted">-</span>
                                                    </td>
                                                    <td>{{ $customer->created_at->format('Y-m-d') }}</td>
                                                    <td>
                                                        <div class="d-flex gap-2 flex-wrap">
                                                            <form action="{{ route('admin.customers.create-invoice', $customer->id) }}" method="POST" class="d-inline">
                                                                @csrf
                                                                <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Create invoice with balance amount PKR {{ number_format($customer->balance, 2) }}?')">
                                                                    Invoice
                                                                </button>
                                                            </form>
                                                            
                                                            <a href="{{ route('admin.customers.generate-invoice', $customer->id) }}" class="btn btn-primary btn-sm">
                                                                Generate Invoice
                                                            </a>
                                                            
                                                            <a href="{{ route('admin.customers.invoice-history', $customer->id) }}" class="btn btn-secondary btn-sm" title="View Invoice History">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                            
                                                            <form action="{{ route('admin.customers.destroy', $customer->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this customer?')">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                                            </form>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="9" class="text-center">No customers found. Create your first customer above.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                {{ $customers->links() }}
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

    <!-- Create Customer Modal -->
    <div class="modal fade" id="createCustomerModal" tabindex="-1" aria-labelledby="createCustomerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createCustomerModalLabel">Create New Customer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.customers.store') }}" method="POST" id="createCustomerForm">
                    @csrf
                    <div class="modal-body">
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

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label">Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label">Phone Number</label>
                                    <input type="text" name="phone_number" class="form-control @error('phone_number') is-invalid @enderror" value="{{ old('phone_number') }}" placeholder="e.g., +92 300 1234567">
                                    @error('phone_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label">Payee Name</label>
                                    <input type="text" name="payee_name" class="form-control @error('payee_name') is-invalid @enderror" value="{{ old('payee_name') }}" placeholder="Name of the payee">
                                    @error('payee_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label">Reference ID <span class="text-danger">*</span></label>
                                    <input type="text" name="reference_id" class="form-control @error('reference_id') is-invalid @enderror" value="{{ old('reference_id') }}" required>
                                    @error('reference_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label">Balance</label>
                                    <input type="text" class="form-control" value="PKR 0.00" disabled style="background-color: #e9ecef;">
                                    <small class="form-text text-muted">Balance is automatically set to 0 for new customers</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create Customer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

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
    
    <script>
        // Reset form when modal is closed
        document.addEventListener('DOMContentLoaded', function() {
            const createCustomerModal = document.getElementById('createCustomerModal');
            const createCustomerForm = document.getElementById('createCustomerForm');
            
            createCustomerModal.addEventListener('hidden.bs.modal', function () {
                createCustomerForm.reset();
                // Clear validation errors
                const invalidInputs = createCustomerForm.querySelectorAll('.is-invalid');
                invalidInputs.forEach(input => {
                    input.classList.remove('is-invalid');
                });
                const errorMessages = createCustomerForm.querySelectorAll('.invalid-feedback');
                errorMessages.forEach(msg => msg.remove());
            });
        });
    </script>
</body>
</html>

