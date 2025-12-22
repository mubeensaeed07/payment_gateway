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
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
                                                            <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#manageSlabsModal{{ $user->id }}" onclick="setTimeout(function() { if (typeof window.loadSlabs === 'function') { window.loadSlabs({{ $user->id }}); } else { console.error('loadSlabs function not found'); } }, 300);">
                                                                <i class="fa fa-cog"></i> Manage Slabs
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
                                                                    <div class="alert alert-info">
                                                                        <strong>Note:</strong> Slab ranges and 1Link fees are fixed. You can only set the Charge for each slab.
                                                                    </div>
                                                                    <div id="slabsContainer{{ $user->id }}">
                                                                        <!-- Slabs will be loaded here via JavaScript -->
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
        // Define functions FIRST, before they are used
        
        // Define addSlabRow function globally (must be defined before loadSlabs uses it)
        window.addSlabRow = function(adminId, slab) {
            const container = document.getElementById(`slabsContainer${adminId}`);
            if (!container) {
                console.error('Container not found in addSlabRow for admin:', adminId);
                return;
            }
            const existingSlabs = container.querySelectorAll('.slab-row').length;
            const slabIndex = existingSlabs;
            
            const fromAmount = slab.from_amount || 0;
            const toAmount = slab.to_amount !== null && slab.to_amount !== undefined ? slab.to_amount : '';
            const toAmountDisplay = toAmount === '' ? 'Unlimited' : toAmount.toLocaleString();
            const label = slab.label || `Slab ${slab.slab_number}`;
            const onelinkFee = slab.onelink_fee || 0;
            const charge = slab.charge || 0;
            const totalCharge = parseFloat(charge) + parseFloat(onelinkFee);
            
            const row = document.createElement('div');
            row.className = 'slab-row mb-3 p-3 border rounded';
            row.innerHTML = `
                <div class="row align-items-end">
                    <div class="col-md-2">
                        <label class="form-label"><strong>${label}</strong></label>
                        <input type="hidden" name="slabs[${slabIndex}][slab_number]" value="${slab.slab_number}">
                        <input type="hidden" name="slabs[${slabIndex}][from_amount]" value="${fromAmount}">
                        <input type="hidden" name="slabs[${slabIndex}][to_amount]" value="${toAmount}">
                        <small class="text-muted">${fromAmount.toLocaleString()}${toAmount !== '' ? ' - ' + toAmountDisplay : '+'}</small>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Fee Applied to Aggregator - by 1Link</label>
                        <input type="text" class="form-control" value="${onelinkFee.toFixed(2)}" readonly style="background-color: #f0f0f0;">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Charge</label>
                        <input type="number" name="slabs[${slabIndex}][charge]" class="form-control slab-charge" step="0.01" min="0" value="${charge}" required oninput="window.updateTotalCharge(this)">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Total Charge</label>
                        <input type="text" class="form-control slab-total" value="${totalCharge.toFixed(2)}" readonly style="background-color: #e8f5e9; font-weight: bold;">
                    </div>
                </div>
            `;
            container.appendChild(row);
        };
        
        // Define updateTotalCharge function globally
        window.updateTotalCharge = function(input) {
            const row = input.closest('.slab-row');
            const chargeInput = row.querySelector('.slab-charge');
            const totalInput = row.querySelector('.slab-total');
            
            // Get 1Link fee from the readonly input in the same row
            const onelinkFeeRow = row.querySelector('input[readonly]');
            const onelinkFee = parseFloat(onelinkFeeRow ? onelinkFeeRow.value : 0);
            const charge = parseFloat(chargeInput.value) || 0;
            const total = charge + onelinkFee;
            
            totalInput.value = total.toFixed(2);
        };

        // Load slabs when modal is opened
        document.addEventListener('DOMContentLoaded', function() {
            // Add event listener for all manage slabs modals using Bootstrap 5 events
            @foreach($users as $user)
            const manageSlabsModal{{ $user->id }} = document.getElementById('manageSlabsModal{{ $user->id }}');
            if (manageSlabsModal{{ $user->id }}) {
                // Use shown.bs.modal (after modal is fully shown) for reliability
                manageSlabsModal{{ $user->id }}.addEventListener('shown.bs.modal', function() {
                    console.log('Modal shown for admin {{ $user->id }}');
                    window.loadSlabs({{ $user->id }});
                });
            }
            
            // Also attach click handler to the button as fallback
            const manageSlabsBtn{{ $user->id }} = document.querySelector('[data-bs-target="#manageSlabsModal{{ $user->id }}"]');
            if (manageSlabsBtn{{ $user->id }}) {
                manageSlabsBtn{{ $user->id }}.addEventListener('click', function() {
                    // Small delay to ensure modal is ready
                    setTimeout(function() {
                        window.loadSlabs({{ $user->id }});
                    }, 100);
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

        // Define loadSlabs function globally
        window.loadSlabs = function(adminId) {
            console.log('loadSlabs called for admin:', adminId);
            const container = document.getElementById(`slabsContainer${adminId}`);
            if (!container) {
                console.error('Container not found for admin:', adminId, 'Looking for: slabsContainer' + adminId);
                // Try again after a short delay
                setTimeout(function() {
                    const retryContainer = document.getElementById(`slabsContainer${adminId}`);
                    if (retryContainer) {
                        console.log('Container found on retry');
                        window.loadSlabs(adminId);
                    }
                }, 500);
                return;
            }
            
            console.log('Container found, loading slabs...');
            
            // Define fixed slabs structure
            const fixedSlabs = [
                {slab_number: 1, from_amount: 0, to_amount: 10000, label: 'Up to 10K', onelink_fee: 12.5, charge: 0},
                {slab_number: 2, from_amount: 10000, to_amount: 100000, label: '10K+ to 100K', onelink_fee: 31.25, charge: 0},
                {slab_number: 3, from_amount: 100000, to_amount: 250000, label: '100K+ to 250K', onelink_fee: 62.5, charge: 0},
                {slab_number: 4, from_amount: 250000, to_amount: 1000000, label: '250K+ to 1Mln', onelink_fee: 125, charge: 0},
                {slab_number: 5, from_amount: 1000000, to_amount: 2500000, label: '1M+ to 2.5M', onelink_fee: 250, charge: 0},
                {slab_number: 6, from_amount: 2500000, to_amount: 5000000, label: '2.5M+ to 5M', onelink_fee: 375, charge: 0},
                {slab_number: 7, from_amount: 5000000, to_amount: null, label: '5M+', onelink_fee: 500, charge: 0}
            ];
            
            // First, show all fixed slabs immediately (with default charge 0)
            container.innerHTML = '';
            fixedSlabs.forEach((slab, index) => {
                console.log('Adding slab row:', index + 1, slab);
                window.addSlabRow(adminId, slab);
            });
            
            console.log('All slabs added, now fetching existing charges...');
            
            // Then fetch existing charges from API and update them
            fetch(`{{ url('/superadmin/admins') }}/${adminId}/slabs`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                }
            })
            .then(response => {
                console.log('API response status:', response.status);
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                console.log('API data received:', data);
                // Update charges if API returned data
                if (data.slabs && data.slabs.length > 0) {
                    data.slabs.forEach(apiSlab => {
                        const row = container.querySelector(`input[name*="[slab_number]"][value="${apiSlab.slab_number}"]`)?.closest('.slab-row');
                        if (row) {
                            const chargeInput = row.querySelector('input[name*="[charge]"]');
                            const totalInput = row.querySelector('.slab-total');
                            if (chargeInput) {
                                chargeInput.value = apiSlab.charge || 0;
                                // Update total charge
                                const onelinkFee = parseFloat(apiSlab.onelink_fee || 0);
                                const charge = parseFloat(apiSlab.charge || 0);
                                if (totalInput) {
                                    totalInput.value = (charge + onelinkFee).toFixed(2);
                                }
                            }
                        }
                    });
                }
            })
            .catch(error => {
                console.error('Error loading slabs from API:', error);
                // Slabs are already shown, so we just log the error
            });
        };

        // Removed addSlab function - slabs are now fixed
        // addSlabRow and updateTotalCharge are already defined above

        // Removed removeSlab function - slabs are now fixed and cannot be removed

        function saveSlabs(adminId) {
            const container = document.getElementById(`slabsContainer${adminId}`);
            const slabRows = container.querySelectorAll('.slab-row');
            
            // Collect data from DOM directly
            const slabs = [];
            const slabNumbers = new Set();
            
            slabRows.forEach((row, index) => {
                const slabNumberInput = row.querySelector('input[name*="[slab_number]"]');
                const chargeInput = row.querySelector('input[name*="[charge]"]');
                
                if (!slabNumberInput || !chargeInput) {
                    return; // Skip invalid rows
                }
                
                const slabNumber = parseInt(slabNumberInput.value) || (index + 1);
                const charge = parseFloat(chargeInput.value) || 0;
                
                slabs.push({
                    slab_number: slabNumber,
                    charge: charge
                });
                
                slabNumbers.add(slabNumber);
            });
            
            if (slabs.length === 0) {
                alert('Please add at least one slab');
                return;
            }
            
            // Validate slab numbers are sequential (1-7)
            const sortedNumbers = Array.from(slabNumbers).sort((a, b) => a - b);
            if (sortedNumbers.length !== 7) {
                alert('All 7 slabs must be configured');
                return;
            }
            for (let i = 0; i < sortedNumbers.length; i++) {
                if (sortedNumbers[i] !== i + 1) {
                    alert('Slab numbers must be sequential from 1 to 7');
                    return;
                }
            }
            
            // No need to validate ranges - they are fixed
            // Removed overlap validation
            
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


