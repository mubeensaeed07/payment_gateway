<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Start - Title -->
    <title>Manage Admins - {{ config('app.name') }}</title>
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
                            <h4 class="text-black">Manage Admins</h4>
                        </div>
                    </div>
                    <div class="col-xl-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Create New Admin</h4>
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

                                <form action="{{ route('superadmin.users.create') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="role" value="admin">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group mb-3">
                                                <label class="form-label">Name</label>
                                                <input type="text" name="name" class="form-control" required>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group mb-3">
                                                <label class="form-label">Email</label>
                                                <input type="email" name="email" class="form-control" required>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group mb-3">
                                                <label class="form-label">Prefix Number <small class="text-muted">(4-6 digits)</small></label>
                                                <input type="text" name="prefix_number" class="form-control @error('prefix_number') is-invalid @enderror" pattern="[0-9]{4,6}" placeholder="e.g., 3545" maxlength="6" value="{{ old('prefix_number') }}">
                                                @error('prefix_number')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                <small class="form-text text-muted">Optional: Prefix for customer numbers (e.g., 3545 will make customer numbers like 35451003)</small>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Create Admin</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">All Admins</h4>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-responsive-md">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Prefix Number</th>
                                                <th>Status</th>
                                                <th>Created</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($users as $user)
                                                <tr>
                                                    <td>{{ $user->name }}</td>
                                                    <td>{{ $user->email }}</td>
                                                    <td>
                                                        @if($user->prefix_number)
                                                            <span class="badge bg-info">{{ $user->prefix_number }}</span>
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($user->google_id)
                                                            <span class="badge badge-success">Active</span>
                                                        @else
                                                            <span class="badge badge-warning">Pending</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $user->created_at->format('Y-m-d') }}</td>
                                                    <td>
                                                        <div class="d-flex gap-2">
                                                            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editPrefixModal{{ $user->id }}">
                                                                Edit Prefix
                                                            </button>
                                                            <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#manageSlabsModal{{ $user->id }}">
                                                                Manage Slabs
                                                            </button>
                                                            <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#manageExternalProviderModal{{ $user->id }}">
                                                                External Provider
                                                            </button>
                                                            <form action="{{ route('superadmin.users.delete', $user->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this admin?')">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                                            </form>
                                                        </div>
                                                    </td>
                                                </tr>
                                                
                                                <!-- Edit Prefix Modal -->
                                                <div class="modal fade" id="editPrefixModal{{ $user->id }}" tabindex="-1" aria-labelledby="editPrefixModalLabel{{ $user->id }}" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="editPrefixModalLabel{{ $user->id }}">Edit Prefix Number for {{ $user->name }}</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <form action="{{ route('superadmin.users.update-prefix', $user->id) }}" method="POST">
                                                                @csrf
                                                                @method('PUT')
                                                                <div class="modal-body">
                                                                    <div class="form-group mb-3">
                                                                        <label class="form-label">Prefix Number <small class="text-muted">(4-6 digits)</small></label>
                                                                        <input type="text" name="prefix_number" class="form-control" pattern="[0-9]{4,6}" placeholder="e.g., 3545" maxlength="6" value="{{ $user->prefix_number }}">
                                                                        <small class="form-text text-muted">Leave empty to remove prefix. Customer numbers will include this prefix (e.g., 35451003)</small>
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                    <button type="submit" class="btn btn-primary">Update Prefix</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <!-- Manage Slabs Modal -->
                                                <div class="modal fade" id="manageSlabsModal{{ $user->id }}" tabindex="-1" aria-labelledby="manageSlabsModalLabel{{ $user->id }}" aria-hidden="true">
                                                    <div class="modal-dialog modal-lg">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="manageSlabsModalLabel{{ $user->id }}">Manage Slabs for {{ $user->name }}</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <form id="slabsForm{{ $user->id }}">
                                                                    <div id="slabsContainer{{ $user->id }}">
                                                                        <!-- Slabs will be loaded here via JavaScript -->
                                                                    </div>
                                                                    <div class="mt-3">
                                                                        <button type="button" class="btn btn-sm btn-success" onclick="addSlab({{ $user->id }})">
                                                                            <i class="fa fa-plus"></i> Add Slab
                                                                        </button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                <button type="button" class="btn btn-primary" onclick="saveSlabs({{ $user->id }})">Save Slabs</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Manage External Provider Modal -->
                                                <div class="modal fade" id="manageExternalProviderModal{{ $user->id }}" tabindex="-1" aria-labelledby="manageExternalProviderModalLabel{{ $user->id }}" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="manageExternalProviderModalLabel{{ $user->id }}">External Provider Credentials for {{ $user->name }}</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <form id="externalProviderForm{{ $user->id }}">
                                                                    <div class="mb-3">
                                                                        <label class="form-label">Username <span class="text-danger">*</span></label>
                                                                        <input type="text" name="username" class="form-control" id="externalProviderUsername{{ $user->id }}" required>
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label class="form-label">Password <span class="text-danger">*</span></label>
                                                                        <input type="password" name="password" class="form-control" id="externalProviderPassword{{ $user->id }}" required>
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label class="form-label">Bill Enquiry API URL <span class="text-danger">*</span></label>
                                                                        <input type="url" name="bill_enquiry_url" class="form-control" id="externalProviderEnquiryUrl{{ $user->id }}" placeholder="https://example.com/api/bill-enquiry" required>
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label class="form-label">Bill Payment API URL <span class="text-danger">*</span></label>
                                                                        <input type="url" name="bill_payment_url" class="form-control" id="externalProviderPaymentUrl{{ $user->id }}" placeholder="https://example.com/api/bill-payment" required>
                                                                    </div>
                                                                    <div class="alert alert-info">
                                                                        <small><strong>Note:</strong> When invoice numbers end with "02", the system will route requests to these external provider APIs.</small>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                <button type="button" class="btn btn-primary" onclick="saveExternalProvider({{ $user->id }})">Save Credentials</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="text-center">No admins found</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                {{ $users->links() }}
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
    
    <script>
        // Load slabs when modal is opened
        document.addEventListener('DOMContentLoaded', function() {
            // Add event listener for all manage slabs modals
            @foreach($users as $user)
            const manageSlabsModal{{ $user->id }} = document.getElementById('manageSlabsModal{{ $user->id }}');
            if (manageSlabsModal{{ $user->id }}) {
                manageSlabsModal{{ $user->id }}.addEventListener('show.bs.modal', function() {
                    loadSlabs({{ $user->id }});
                });
            }
            
            // Add event listener for external provider modal
            const manageExternalProviderModal{{ $user->id }} = document.getElementById('manageExternalProviderModal{{ $user->id }}');
            if (manageExternalProviderModal{{ $user->id }}) {
                manageExternalProviderModal{{ $user->id }}.addEventListener('show.bs.modal', function() {
                    loadExternalProvider({{ $user->id }});
                });
            }
            @endforeach
        });

        function loadSlabs(adminId) {
            fetch(`{{ url('/superadmin/admins') }}/${adminId}/slabs`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                const container = document.getElementById(`slabsContainer${adminId}`);
                container.innerHTML = '';
                
                if (data.slabs && data.slabs.length > 0) {
                    data.slabs.forEach(slab => {
                        addSlabRow(adminId, slab);
                    });
                } else {
                    // Add default 5 slabs
                    for (let i = 1; i <= 5; i++) {
                        addSlabRow(adminId, {
                            slab_number: i,
                            from_amount: (i - 1) * 10000,
                            to_amount: i === 5 ? null : i * 10000,
                            charge: 0
                        });
                    }
                }
            })
            .catch(error => {
                console.error('Error loading slabs:', error);
                // Add default 5 slabs on error
                const container = document.getElementById(`slabsContainer${adminId}`);
                container.innerHTML = '';
                for (let i = 1; i <= 5; i++) {
                    addSlabRow(adminId, {
                        slab_number: i,
                        from_amount: (i - 1) * 10000,
                        to_amount: i === 5 ? null : i * 10000,
                        charge: 0
                    });
                }
            });
        }

        function addSlab(adminId) {
            const container = document.getElementById(`slabsContainer${adminId}`);
            const existingSlabs = container.querySelectorAll('.slab-row').length;
            
            if (existingSlabs >= 6) {
                alert('Maximum 6 slabs allowed');
                return;
            }
            
            const slabNumber = existingSlabs + 1;
            const lastSlab = container.querySelector('.slab-row:last-child');
            let fromAmount = 0;
            
            if (lastSlab) {
                const lastToAmount = parseFloat(lastSlab.querySelector('input[name*="[to_amount]"]').value) || 0;
                fromAmount = lastToAmount + 0.01;
            }
            
            addSlabRow(adminId, {
                slab_number: slabNumber,
                from_amount: fromAmount,
                to_amount: null,
                charge: 0
            });
        }

        function addSlabRow(adminId, slab) {
            const container = document.getElementById(`slabsContainer${adminId}`);
            const existingSlabs = container.querySelectorAll('.slab-row').length;
            const slabIndex = existingSlabs;
            
            const row = document.createElement('div');
            row.className = 'slab-row mb-3 p-3 border rounded';
            row.innerHTML = `
                <div class="row align-items-end">
                    <div class="col-md-2">
                        <label class="form-label">Slab ${slab.slab_number}</label>
                        <input type="hidden" name="slabs[${slabIndex}][slab_number]" value="${slab.slab_number}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">From Amount</label>
                        <input type="number" name="slabs[${slabIndex}][from_amount]" class="form-control" step="0.01" min="0" value="${slab.from_amount || 0}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">To Amount <small class="text-muted">(Leave empty for last slab)</small></label>
                        <input type="number" name="slabs[${slabIndex}][to_amount]" class="form-control" step="0.01" min="0" value="${slab.to_amount || ''}" ${slab.to_amount === null ? '' : ''}>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Charge</label>
                        <input type="number" name="slabs[${slabIndex}][charge]" class="form-control" step="0.01" min="0" value="${slab.charge || 0}" required>
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-sm btn-danger" onclick="removeSlab(this)">
                            <i class="fa fa-times"></i>
                        </button>
                    </div>
                </div>
            `;
            container.appendChild(row);
        }

        function removeSlab(button) {
            const container = button.closest('#slabsContainer' + button.closest('.modal').id.replace('manageSlabsModal', ''));
            const row = button.closest('.slab-row');
            row.remove();
            
            // Renumber remaining slabs
            const rows = container.querySelectorAll('.slab-row');
            rows.forEach((row, index) => {
                const slabNumber = index + 1;
                row.querySelector('label').textContent = `Slab ${slabNumber}`;
                row.querySelector('input[name*="[slab_number]"]').value = slabNumber;
            });
        }

        function saveSlabs(adminId) {
            const container = document.getElementById(`slabsContainer${adminId}`);
            const slabRows = container.querySelectorAll('.slab-row');
            
            // Collect data from DOM directly
            const slabs = [];
            const slabNumbers = new Set();
            
            slabRows.forEach((row, index) => {
                const slabNumberInput = row.querySelector('input[name*="[slab_number]"]');
                const fromAmountInput = row.querySelector('input[name*="[from_amount]"]');
                const toAmountInput = row.querySelector('input[name*="[to_amount]"]');
                const chargeInput = row.querySelector('input[name*="[charge]"]');
                
                if (!slabNumberInput || !fromAmountInput || !chargeInput) {
                    return; // Skip invalid rows
                }
                
                const slabNumber = parseInt(slabNumberInput.value) || (index + 1);
                const fromAmount = parseFloat(fromAmountInput.value) || 0;
                const toAmountValue = toAmountInput ? toAmountInput.value.trim() : '';
                const toAmount = toAmountValue !== '' ? parseFloat(toAmountValue) : null;
                const charge = parseFloat(chargeInput.value) || 0;
                
                slabs.push({
                    slab_number: slabNumber,
                    from_amount: fromAmount,
                    to_amount: toAmount,
                    charge: charge
                });
                
                slabNumbers.add(slabNumber);
            });
            
            if (slabs.length === 0) {
                alert('Please add at least one slab');
                return;
            }
            
            // Validate slab numbers are sequential
            const sortedNumbers = Array.from(slabNumbers).sort((a, b) => a - b);
            for (let i = 0; i < sortedNumbers.length; i++) {
                if (sortedNumbers[i] !== i + 1) {
                    alert('Slab numbers must be sequential starting from 1');
                    return;
                }
            }
            
            // Validate ranges don't overlap
            for (let i = 0; i < slabs.length; i++) {
                for (let j = i + 1; j < slabs.length; j++) {
                    const slab1 = slabs[i];
                    const slab2 = slabs[j];
                    
                    if (slab1.to_amount !== null && slab2.to_amount !== null) {
                        if ((slab1.from_amount >= slab2.from_amount && slab1.from_amount < slab2.to_amount) ||
                            (slab1.to_amount > slab2.from_amount && slab1.to_amount <= slab2.to_amount) ||
                            (slab2.from_amount >= slab1.from_amount && slab2.from_amount < slab1.to_amount) ||
                            (slab2.to_amount > slab1.from_amount && slab2.to_amount <= slab1.to_amount)) {
                            alert('Slab ranges cannot overlap');
                            return;
                        }
                    }
                }
            }
            
            fetch(`{{ url('/superadmin/admins') }}/${adminId}/slabs`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ slabs: slabs })
            })
            .then(response => {
                return response.json().then(data => {
                    if (!response.ok) {
                        throw new Error(data.message || 'Failed to save slabs');
                    }
                    return data;
                });
            })
            .then(data => {
                if (data.success) {
                    alert('Slabs saved successfully!');
                    const modal = bootstrap.Modal.getInstance(document.getElementById(`manageSlabsModal${adminId}`));
                    modal.hide();
                } else {
                    alert('Error: ' + (data.message || 'Failed to save slabs'));
                }
            })
            .catch(error => {
                console.error('Error saving slabs:', error);
                alert('Error: ' + error.message);
            });
        }

        function loadExternalProvider(adminId) {
            fetch(`{{ url('/superadmin/admins') }}/${adminId}/external-provider`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.external_provider) {
                    document.getElementById(`externalProviderUsername${adminId}`).value = data.external_provider.username || '';
                    document.getElementById(`externalProviderPassword${adminId}`).value = '';
                    document.getElementById(`externalProviderEnquiryUrl${adminId}`).value = data.external_provider.bill_enquiry_url || '';
                    document.getElementById(`externalProviderPaymentUrl${adminId}`).value = data.external_provider.bill_payment_url || '';
                } else {
                    // Clear fields if no external provider exists
                    document.getElementById(`externalProviderUsername${adminId}`).value = '';
                    document.getElementById(`externalProviderPassword${adminId}`).value = '';
                    document.getElementById(`externalProviderEnquiryUrl${adminId}`).value = '';
                    document.getElementById(`externalProviderPaymentUrl${adminId}`).value = '';
                }
            })
            .catch(error => {
                console.error('Error loading external provider:', error);
                // Clear fields on error
                document.getElementById(`externalProviderUsername${adminId}`).value = '';
                document.getElementById(`externalProviderPassword${adminId}`).value = '';
                document.getElementById(`externalProviderEnquiryUrl${adminId}`).value = '';
                document.getElementById(`externalProviderPaymentUrl${adminId}`).value = '';
            });
        }

        function saveExternalProvider(adminId) {
            const username = document.getElementById(`externalProviderUsername${adminId}`).value.trim();
            const password = document.getElementById(`externalProviderPassword${adminId}`).value;
            const billEnquiryUrl = document.getElementById(`externalProviderEnquiryUrl${adminId}`).value.trim();
            const billPaymentUrl = document.getElementById(`externalProviderPaymentUrl${adminId}`).value.trim();

            if (!username || !password || !billEnquiryUrl || !billPaymentUrl) {
                alert('Please fill in all fields');
                return;
            }

            fetch(`{{ url('/superadmin/admins') }}/${adminId}/external-provider`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    username: username,
                    password: password,
                    bill_enquiry_url: billEnquiryUrl,
                    bill_payment_url: billPaymentUrl
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('External provider credentials saved successfully!');
                    const modal = bootstrap.Modal.getInstance(document.getElementById(`manageExternalProviderModal${adminId}`));
                    modal.hide();
                } else {
                    alert('Error: ' + (data.message || 'Failed to save external provider credentials'));
                }
            })
            .catch(error => {
                console.error('Error saving external provider:', error);
                alert('Error saving external provider credentials. Please try again.');
            });
        }
    </script>
</body>
</html>

