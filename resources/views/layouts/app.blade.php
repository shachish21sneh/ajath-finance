<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') | {{ config('app.name', 'Ajath Finance ERP') }}</title>

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome 6 CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">

    <!-- Custom ERP Design System -->
    <link href="{{ asset('assets/css/app.css') }}" rel="stylesheet">

    @stack('styles')
</head>
<body>
    <div class="app-wrapper">
        <!-- Sidebar Navigation -->
        <aside class="sidebar">
            <a href="{{ route('dashboard') }}" class="sidebar-brand">
                <i class="fa-solid fa-shapes me-2"></i>
                <span>Ajath <span class="fw-light opacity-75">ERP</span></span>
            </a>

            <div class="sidebar-menu">
                <div class="sidebar-section-title">Core</div>
                <a href="{{ route('dashboard') }}" class="sidebar-nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="fa-solid fa-gauge-high"></i>
                    <span>Dashboard</span>
                </a>

                <div class="sidebar-section-title">Accounting</div>
                <a href="{{ route('vouchers.index') }}" class="sidebar-nav-link {{ request()->routeIs('vouchers.*') && !request()->has('type') ? 'active' : '' }}">
                    <i class="fa-solid fa-book-journal-whills"></i>
                    <span>All Vouchers</span>
                </a>
                <a href="{{ route('vouchers.create', ['type' => 'CONTRA']) }}" class="sidebar-nav-link">
                    <i class="fa-solid fa-arrow-right-arrow-left text-info"></i>
                    <span>Contra</span>
                    <span class="badge-shortcut">F4</span>
                </a>
                <a href="{{ route('vouchers.create', ['type' => 'PAYMENT']) }}" class="sidebar-nav-link">
                    <i class="fa-solid fa-money-bill-transfer text-danger"></i>
                    <span>Payment</span>
                    <span class="badge-shortcut">F5</span>
                </a>
                <a href="{{ route('vouchers.create', ['type' => 'RECEIPT']) }}" class="sidebar-nav-link">
                    <i class="fa-solid fa-hand-holding-dollar text-success"></i>
                    <span>Receipt</span>
                    <span class="badge-shortcut">F6</span>
                </a>
                <a href="{{ route('vouchers.create', ['type' => 'JOURNAL']) }}" class="sidebar-nav-link">
                    <i class="fa-solid fa-file-lines text-secondary"></i>
                    <span>Journal</span>
                    <span class="badge-shortcut">F7</span>
                </a>

                <div class="sidebar-section-title">Sales & Billing</div>
                <a href="{{ route('sales.index') }}" class="sidebar-nav-link {{ request()->routeIs('sales.index') ? 'active' : '' }}">
                    <i class="fa-solid fa-file-invoice-dollar text-primary"></i>
                    <span>Sales Invoices</span>
                    <span class="badge-shortcut">F8</span>
                </a>
                <a href="{{ route('sales.pos') }}" class="sidebar-nav-link {{ request()->routeIs('sales.pos') ? 'active' : '' }}">
                    <i class="fa-solid fa-cash-register text-warning"></i>
                    <span>POS Counter</span>
                </a>
                <a href="{{ route('customers.index') }}" class="sidebar-nav-link {{ request()->routeIs('customers.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-users"></i>
                    <span>Customers</span>
                </a>

                <div class="sidebar-section-title">Purchases</div>
                <a href="{{ route('purchases.index') }}" class="sidebar-nav-link {{ request()->routeIs('purchases.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-cart-flatbed text-warning"></i>
                    <span>Purchase Invoices</span>
                    <span class="badge-shortcut">F9</span>
                </a>
                <a href="{{ route('suppliers.index') }}" class="sidebar-nav-link {{ request()->routeIs('suppliers.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-truck-field"></i>
                    <span>Suppliers</span>
                </a>

                <div class="sidebar-section-title">Masters & Inventory</div>
                <a href="{{ route('ledgers.index') }}" class="sidebar-nav-link {{ request()->routeIs('ledgers.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-book"></i>
                    <span>Chart of Accounts</span>
                </a>
                <a href="{{ route('products.index') }}" class="sidebar-nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-boxes-stacked"></i>
                    <span>Products & Services</span>
                </a>
                <a href="{{ route('warehouses.index') }}" class="sidebar-nav-link {{ request()->routeIs('warehouses.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-warehouse"></i>
                    <span>Godowns / Depots</span>
                </a>

                <div class="sidebar-section-title">Banking & Cash</div>
                <a href="{{ route('banking.index') }}" class="sidebar-nav-link {{ request()->routeIs('banking.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-building-columns text-info"></i>
                    <span>Bank & Cash Books</span>
                </a>

                <div class="sidebar-section-title">Financial Reports</div>
                <a href="{{ route('reports.day-book') }}" class="sidebar-nav-link {{ request()->routeIs('reports.day-book') ? 'active' : '' }}">
                    <i class="fa-solid fa-calendar-day"></i>
                    <span>Day Book</span>
                </a>
                <a href="{{ route('reports.trial-balance') }}" class="sidebar-nav-link {{ request()->routeIs('reports.trial-balance') ? 'active' : '' }}">
                    <i class="fa-solid fa-scale-balanced"></i>
                    <span>Trial Balance</span>
                </a>
                <a href="{{ route('reports.profit-loss') }}" class="sidebar-nav-link {{ request()->routeIs('reports.profit-loss') ? 'active' : '' }}">
                    <i class="fa-solid fa-chart-line"></i>
                    <span>Profit & Loss</span>
                </a>
                <a href="{{ route('reports.balance-sheet') }}" class="sidebar-nav-link {{ request()->routeIs('reports.balance-sheet') ? 'active' : '' }}">
                    <i class="fa-solid fa-vault"></i>
                    <span>Balance Sheet</span>
                </a>
                <a href="{{ route('reports.gst') }}" class="sidebar-nav-link {{ request()->routeIs('reports.gst') ? 'active' : '' }}">
                    <i class="fa-solid fa-file-shield text-success"></i>
                    <span>GST Summary (GSTR-1)</span>
                </a>

                <div class="sidebar-section-title">Administration</div>
                <a href="{{ route('companies.index') }}" class="sidebar-nav-link {{ request()->routeIs('companies.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-building"></i>
                    <span>Companies</span>
                    <span class="badge-shortcut">F2</span>
                </a>
                <a href="{{ route('users.index') }}" class="sidebar-nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-user-shield"></i>
                    <span>Users & RBAC</span>
                </a>
                <a href="{{ route('settings.index') }}" class="sidebar-nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-sliders"></i>
                    <span>System Settings</span>
                </a>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="main-content-wrapper">
            <!-- Top Navigation Bar -->
            <header class="topbar">
                <div class="d-flex align-items-center gap-3">
                    <button class="btn btn-sm btn-outline-secondary d-lg-none" type="button" data-bs-toggle="collapse" data-bs-target=".sidebar">
                        <i class="fa-solid fa-bars"></i>
                    </button>

                    <!-- Global Spotlight Search Button -->
                    <button class="search-trigger-btn" type="button" data-bs-toggle="modal" data-bs-target="#spotlightSearchModal">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <span>Search everything...</span>
                        <kbd>Ctrl+F</kbd>
                    </button>
                </div>

                <div class="d-flex align-items-center gap-3">
                    <!-- Active Company & FY Context Badges -->
                    <div class="company-fy-selector d-none d-md-flex">
                        <span class="active-context-pill" data-bs-toggle="modal" data-bs-target="#companySelectModal" title="Press F2 to switch Company">
                            <i class="fa-solid fa-building"></i>
                            <span>{{ $currentCompany->name ?? 'Select Company' }}</span>
                            <small class="opacity-75 ms-1">F2</small>
                        </span>
                        <span class="active-context-pill" data-bs-toggle="modal" data-bs-target="#fySelectModal" title="Press F3 to switch Financial Year">
                            <i class="fa-solid fa-calendar-check"></i>
                            <span>{{ $currentFinancialYear->title ?? 'Select FY' }}</span>
                            <small class="opacity-75 ms-1">F3</small>
                        </span>
                    </div>

                    <!-- Theme Toggle -->
                    <button class="btn btn-sm btn-outline-secondary rounded-circle" type="button" onclick="toggleTheme()" title="Toggle Dark/Light theme" style="width: 34px; height: 34px;">
                        <i id="themeToggleIcon" class="fa-solid fa-moon"></i>
                    </button>

                    <!-- User Profile Dropdown -->
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown">
                            <i class="fa-solid fa-circle-user text-primary"></i>
                            <span class="d-none d-sm-inline fw-semibold">{{ auth()->user()->name ?? 'Accountant' }}</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                            <li><h6 class="dropdown-header">{{ auth()->user()->email ?? 'user@ajath.com' }}</h6></li>
                            <li><span class="dropdown-item-text text-muted small"><i class="fa-solid fa-shield-halved me-1"></i> {{ auth()->user()->role->name ?? 'Super Admin' }}</span></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="{{ route('profile.show') }}"><i class="fa-solid fa-id-card me-2"></i> My Profile</a></li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger"><i class="fa-solid fa-arrow-right-from-bracket me-2"></i> Logout</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </header>

            <!-- Keyboard Quick Shortcuts Ribbon -->
            <div class="quick-shortcuts-bar no-print">
                <span class="text-muted small fw-semibold me-2"><i class="fa-solid fa-keyboard me-1"></i> Fast Keys:</span>
                <a href="#" class="quick-voucher-btn" data-bs-toggle="modal" data-bs-target="#companySelectModal"><kbd>F2</kbd> Company</a>
                <a href="#" class="quick-voucher-btn" data-bs-toggle="modal" data-bs-target="#fySelectModal"><kbd>F3</kbd> FY</a>
                <a href="{{ route('vouchers.create', ['type' => 'CONTRA']) }}" class="quick-voucher-btn"><kbd>F4</kbd> Contra</a>
                <a href="{{ route('vouchers.create', ['type' => 'PAYMENT']) }}" class="quick-voucher-btn"><kbd>F5</kbd> Payment</a>
                <a href="{{ route('vouchers.create', ['type' => 'RECEIPT']) }}" class="quick-voucher-btn"><kbd>F6</kbd> Receipt</a>
                <a href="{{ route('vouchers.create', ['type' => 'JOURNAL']) }}" class="quick-voucher-btn"><kbd>F7</kbd> Journal</a>
                <a href="{{ route('sales.create') }}" class="quick-voucher-btn"><kbd>F8</kbd> Sales</a>
                <a href="{{ route('purchases.create') }}" class="quick-voucher-btn"><kbd>F9</kbd> Purchase</a>
                <span class="quick-voucher-btn text-muted"><kbd>Ctrl+S</kbd> Save</span>
                <span class="quick-voucher-btn text-muted"><kbd>Ctrl+P</kbd> Print</span>
                <span class="quick-voucher-btn text-muted"><kbd>Ctrl+F</kbd> Search</span>
            </div>

            <!-- Page Body Content -->
            <main class="p-3 p-md-4 flex-grow-1">
                <!-- Alerts / Flash Messages -->
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
                        <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
                        <i class="fa-solid fa-triangle-exclamation me-2"></i> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
                        <i class="fa-solid fa-triangle-exclamation me-2"></i> <strong>Please check the form for errors:</strong>
                        <ul class="mb-0 mt-1 small">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @yield('content')
            </main>

            <footer class="p-3 text-center text-muted small border-top bg-card-bg no-print">
                <span>&copy; {{ date('Y') }} {{ config('app.name', 'Ajath Finance ERP') }} &bull; Desktop Productivity for Cloud Accounting &bull; Clean Architecture on Laravel 12 & MySQL 8</span>
            </footer>
        </div>
    </div>

    <!-- Spotlight Search Modal (Ctrl + F / Ctrl + K) -->
    <div class="modal fade spotlight-modal" id="spotlightSearchModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="spotlight-input-wrapper">
                    <i class="fa-solid fa-magnifying-glass text-muted"></i>
                    <input type="text" id="spotlightSearchInput" class="spotlight-input" placeholder="Type voucher number, party name, invoice, product, phone or GSTIN..." onkeyup="handleSpotlightSearch(this.value)">
                    <kbd class="text-muted small">ESC</kbd>
                </div>
                <div id="spotlightResultsContainer" class="spotlight-results">
                    <div class="text-center text-muted py-4">
                        <i class="fa-solid fa-keyboard fa-2x mb-2 opacity-50"></i>
                        <p class="small mb-0">Type to instantly search vouchers, invoices, customers, suppliers, ledgers, products...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Company Switcher Modal (F2) -->
    <div class="modal fade" id="companySelectModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content card-modern">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fs-6 fw-bold"><i class="fa-solid fa-building me-2 text-primary"></i> Select Active Company (F2)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-3">
                    <div class="list-group list-group-flush">
                        @foreach(\App\Models\Company::where('is_active', true)->get() as $c)
                            <form action="{{ route('companies.switch', $c->id) }}" method="POST" class="w-100 mb-2">
                                @csrf
                                <button type="submit" class="list-group-item list-group-item-action rounded border p-3 d-flex align-items-center justify-content-between {{ ($currentCompany?->id == $c->id) ? 'border-primary bg-primary-subtle' : '' }}">
                                    <div>
                                        <div class="fw-bold">{{ $c->name }}</div>
                                        <div class="text-muted small">GSTIN: {{ $c->gstin ?: 'Unregistered' }} &bull; State: {{ $c->state }} ({{ $c->state_code }})</div>
                                    </div>
                                    @if($currentCompany?->id == $c->id)
                                        <span class="badge bg-primary"><i class="fa-solid fa-check me-1"></i> Active</span>
                                    @endif
                                </button>
                            </form>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Financial Year Switcher Modal (F3) -->
    <div class="modal fade" id="fySelectModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content card-modern">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fs-6 fw-bold"><i class="fa-solid fa-calendar-check me-2 text-primary"></i> Select Financial Year (F3)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-3">
                    <div class="list-group list-group-flush">
                        @if($currentCompany)
                            @foreach($currentCompany->financialYears as $fy)
                                <form action="{{ route('financial-years.switch', $fy->id) }}" method="POST" class="w-100 mb-2">
                                    @csrf
                                    <button type="submit" class="list-group-item list-group-item-action rounded border p-3 d-flex align-items-center justify-content-between {{ ($currentFinancialYear?->id == $fy->id) ? 'border-primary bg-primary-subtle' : '' }}">
                                        <div>
                                            <div class="fw-bold">{{ $fy->title }}</div>
                                            <div class="text-muted small">{{ $fy->start_date->format('d-M-Y') }} to {{ $fy->end_date->format('d-M-Y') }}</div>
                                        </div>
                                        @if($currentFinancialYear?->id == $fy->id)
                                            <span class="badge bg-primary"><i class="fa-solid fa-check me-1"></i> Current</span>
                                        @endif
                                    </button>
                                </form>
                            @endforeach
                        @else
                            <div class="text-muted text-center py-3">Please select a company first.</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Core JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.5/dist/cdn.min.js"></script>

    <!-- Desktop Keyboard Engine -->
    <script src="{{ asset('assets/js/shortcuts.js') }}"></script>

    @stack('scripts')
</body>
</html>
