	<!-- Start - Header -->
<div class="header">
    <div class="header-content">
        <nav class="navbar navbar-expand">
            <div class="collapse navbar-collapse justify-content-between">
                <div class="header-left">
                    <div class="dashboard_bar">
                        Dashboard
                    </div>
                </div>

                <ul class="navbar-nav header-right">
                    <li class="nav-item dropdown header-profile">
                        <a class="nav-link" href="javascript:void(0);" role="button" data-bs-toggle="dropdown">
                            <img src="{{ asset('assets/images/profile/pic1.webp') }}" width="20" alt="">
                        </a>
                        <div class="dropdown-menu dropdown-menu-end">
                            @auth
                                <div class="dropdown-header px-3 py-2">
                                    <h6 class="mb-0 fw-semibold">{{ auth()->user()->name }}</h6>
                                    <small class="text-muted">
                                        @if(auth()->user()->isSuperAdmin())
                                            Super Admin
                                        @elseif(auth()->user()->isAdmin())
                                            Admin
                                        @else
                                            Reseller
                                        @endif
                                    </small>
                                </div>
                                <div class="dropdown-divider"></div>
                            @endauth
                            <form action="{{ route('logout') }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="dropdown-item ai-icon w-100 text-start border-0 bg-transparent">
                                <svg id="icon-logout" xmlns="http://www.w3.org/2000/svg" class="text-danger" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                                <span class="ms-2">Logout </span>
                            </button>
                            </form>
                        </div>
                    </li>
                </ul>
            </div>
        </nav>
    </div>
</div>