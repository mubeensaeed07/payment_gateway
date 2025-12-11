<div class="deznav">
    <div class="deznav-scroll">
        <ul class="metismenu" id="menu">
            @auth
                @if(auth()->user()->isSuperAdmin())
                    <li>
                        <a href="{{ route('superadmin.dashboard') }}" class="ai-icon" aria-expanded="false">
                            <i class="flaticon-381-networking"></i>
                            <span class="nav-text">Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('superadmin.admins') }}" class="ai-icon" aria-expanded="false">
                            <i class="flaticon-381-user-7"></i>
                            <span class="nav-text">Manage Admins</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('superadmin.resellers') }}" class="ai-icon" aria-expanded="false">
                            <i class="flaticon-381-user-8"></i>
                            <span class="nav-text">Manage Resellers</span>
                        </a>
                    </li>
                    @if(!request()->routeIs('superadmin.dashboard'))
                        <li>
                            <a href="{{ route('superadmin.dashboard') }}" class="ai-icon" aria-expanded="false">
                                <i class="flaticon-381-networking"></i>
                                <span class="nav-text">Back to Dashboard</span>
                            </a>
                        </li>
                    @endif
                @elseif(auth()->user()->isAdmin())
                    <li>
                        <a href="{{ route('admin.dashboard') }}" class="ai-icon" aria-expanded="false">
                            <i class="flaticon-381-networking"></i>
                            <span class="nav-text">Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.customers.index') }}" class="ai-icon" aria-expanded="false">
                            <i class="flaticon-381-user-8"></i>
                            <span class="nav-text">Manage Customers</span>
                        </a>
                    </li>
                    <li>
                        <a class="has-arrow ai-icon" href="javascript:void(0);" aria-expanded="false">
                            <i class="flaticon-381-file"></i>
                            <span class="nav-text">Reports</span>
                        </a>
                        <ul aria-expanded="false">
                            <li><a href="{{ route('admin.reports.non-paid-invoices') }}">Non Paid Invoices</a></li>
                            <li><a href="{{ route('admin.reports.paid-invoices') }}">Paid Invoices</a></li>
                            <li><a href="{{ route('admin.reports.all-invoices') }}">Invoices</a></li>
                        </ul>
                    </li>
                @else
                    <li>
                        <a href="{{ route('reseller.dashboard') }}" class="ai-icon" aria-expanded="false">
                            <i class="flaticon-381-networking"></i>
                            <span class="nav-text">Dashboard</span>
                        </a>
                    </li>
                @endif
            @endauth
        </ul>
    </div>
</div>