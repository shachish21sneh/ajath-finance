@extends('layouts.app')

@section('title', 'Executive Dashboard')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-3">
    <div>
        <h4 class="fw-bold mb-1">Financial Overview & Dashboard</h4>
        <p class="text-muted small mb-0">Live accounting metrics for <strong>{{ $company->name ?? 'Default Company' }}</strong> ({{ $currentFinancialYear->title ?? 'Current FY' }})</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('sales.create') }}" class="btn btn-primary btn-sm fw-semibold shadow-sm">
            <i class="fa-solid fa-plus me-1"></i> New Sales Bill (F8)
        </a>
        <a href="{{ route('vouchers.create', ['type' => 'RECEIPT']) }}" class="btn btn-outline-success btn-sm fw-semibold">
            <i class="fa-solid fa-hand-holding-dollar me-1"></i> Quick Receipt (F6)
        </a>
    </div>
</div>

<!-- Financial KPI Stat Cards -->
<div class="row g-3 mb-4">
    <!-- Total Sales -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card card-modern stat-card accent-sales">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="stat-label mb-0">Total Sales (Turnover)</span>
                <div class="stat-icon bg-primary-subtle text-primary">
                    <i class="fa-solid fa-file-invoice-dollar"></i>
                </div>
            </div>
            <div>
                <div class="stat-value text-primary text-nowrap">₹ {{ number_format($metrics['sales_total'], 2) }}</div>
                <div class="d-flex align-items-center justify-content-between mt-2 pt-2 border-top border-light-subtle">
                    <span class="badge bg-primary-subtle text-primary small"><i class="fa-solid fa-arrow-trend-up me-1"></i> Active FY</span>
                    <span class="text-muted small">Tax Invoices</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Purchases -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card card-modern stat-card accent-purchases">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="stat-label mb-0">Total Purchases</span>
                <div class="stat-icon bg-warning-subtle text-warning">
                    <i class="fa-solid fa-cart-flatbed"></i>
                </div>
            </div>
            <div>
                <div class="stat-value text-warning-emphasis text-nowrap">₹ {{ number_format($metrics['purchase_total'], 2) }}</div>
                <div class="d-flex align-items-center justify-content-between mt-2 pt-2 border-top border-light-subtle">
                    <span class="badge bg-warning-subtle text-warning-emphasis small"><i class="fa-solid fa-cart-shopping me-1"></i> Procurement</span>
                    <span class="text-muted small">Inward Bills</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Outstanding Receivables -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card card-modern stat-card accent-receivables">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="stat-label mb-0">Receivables (Debtors)</span>
                <div class="stat-icon bg-danger-subtle text-danger">
                    <i class="fa-solid fa-hand-holding-dollar"></i>
                </div>
            </div>
            <div>
                <div class="stat-value text-danger text-nowrap">₹ {{ number_format($metrics['receivables'], 2) }}</div>
                <div class="d-flex align-items-center justify-content-between mt-2 pt-2 border-top border-light-subtle">
                    <span class="badge bg-danger-subtle text-danger small"><i class="fa-solid fa-clock me-1"></i> Due From Debtors</span>
                    <span class="text-muted small">Pending</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Liquid Bank & Cash -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card card-modern stat-card accent-liquid">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="stat-label mb-0">Liquid Bank & Cash</span>
                <div class="stat-icon bg-success-subtle text-success">
                    <i class="fa-solid fa-building-columns"></i>
                </div>
            </div>
            <div>
                <div class="stat-value text-success text-nowrap">₹ {{ number_format($metrics['cash_balance'] + $metrics['bank_balance'], 2) }}</div>
                <div class="d-flex align-items-center justify-content-between mt-2 pt-2 border-top border-light-subtle">
                    <span class="badge bg-success-subtle text-success small"><i class="fa-solid fa-building-columns me-1"></i> Bank: ₹ {{ number_format($metrics['bank_balance'], 0) }}</span>
                    <span class="badge bg-success-subtle text-success small"><i class="fa-solid fa-money-bill me-1"></i> Cash: ₹ {{ number_format($metrics['cash_balance'], 0) }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Interactive Chart.js Visualizations -->
<div class="row g-3 g-md-4 mb-4">
    <div class="col-12 col-lg-8">
        <div class="card card-modern p-3 p-md-4 h-100">
            <div class="d-flex flex-wrap align-items-center justify-content-between mb-3 gap-2">
                <h6 class="fw-bold mb-0"><i class="fa-solid fa-chart-column me-2 text-primary"></i> Monthly Sales & Purchase Flow</h6>
                <span class="badge bg-light text-muted border">FY 2025-26</span>
            </div>
            <div class="chart-container-responsive">
                <canvas id="salesPurchaseChart"></canvas>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-4">
        <div class="card card-modern p-3 p-md-4 h-100">
            <div class="d-flex flex-wrap align-items-center justify-content-between mb-3 gap-2">
                <h6 class="fw-bold mb-0"><i class="fa-solid fa-chart-pie me-2 text-primary"></i> Top Products by Stock</h6>
                <span class="badge bg-light text-muted border">Live Stock</span>
            </div>
            <div class="chart-container-responsive">
                <div class="doughnut-wrapper">
                    <canvas id="topProductsChart"></canvas>
                    <div class="doughnut-center-info">
                        <div class="doughnut-center-number">{{ number_format($metrics['top_products']->sum('current_stock'), 0) }}</div>
                        <div class="doughnut-center-text">Units in Stock</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Transactions & Interactive Productivity Panel -->
<div class="row g-3 g-md-4">
    <div class="col-12 col-lg-8">
        <div class="card card-modern p-3 p-md-4 h-100">
            <div class="d-flex flex-wrap align-items-center justify-content-between mb-3 gap-2">
                <h6 class="fw-bold mb-0"><i class="fa-solid fa-clock-rotate-left me-2 text-primary"></i> Recent Voucher Transactions</h6>
                <a href="{{ route('vouchers.index') }}" class="btn btn-sm btn-link text-decoration-none">View All Vouchers <i class="fa-solid fa-arrow-right ms-1"></i></a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 dashboard-table-compact">
                    <thead>
                        <tr>
                            <th style="width: 70px;">Type</th>
                            <th style="width: 88px;">Voucher #</th>
                            <th style="width: 78px;">Date</th>
                            <th>Particulars / Ledger</th>
                            <th class="text-end" style="width: 100px;">Amount</th>
                            <th class="text-center" style="width: 38px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($metrics['recent_vouchers'] as $v)
                            <tr>
                                <td>
                                    <span class="badge {{ $v->voucher_type->badgeClass() }} py-1 px-2" style="font-size: 0.68rem;">
                                        {{ $v->voucher_type->value }}
                                    </span>
                                </td>
                                <td class="fw-semibold small text-nowrap">{{ $v->voucher_no }}</td>
                                <td class="small text-muted text-nowrap">{{ $v->voucher_date->format('d-M-y') }}</td>
                                <td class="small">
                                    <div class="fw-semibold text-truncate" style="max-width: 200px;" title="{{ $v->partyLedger->name ?? 'Multi-Ledger Entry' }}">
                                        {{ $v->partyLedger->name ?? 'Multi-Ledger Entry' }}
                                    </div>
                                    @if($v->narration)
                                        <div class="text-muted text-truncate" style="max-width: 200px; font-size: 0.72rem;">{{ $v->narration }}</div>
                                    @endif
                                </td>
                                <td class="text-end fw-bold num-align text-nowrap">₹ {{ number_format($v->total_amount, 2) }}</td>
                                <td class="text-center">
                                    <a href="{{ route('vouchers.show', $v->id) }}" class="btn btn-sm btn-light border py-1 px-2 text-muted" title="View details">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No recent vouchers posted.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-4">
        <div class="card card-modern p-3 p-md-4 h-100">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h6 class="fw-bold mb-0"><i class="fa-solid fa-keyboard me-2 text-primary"></i> Productivity Hotkeys</h6>
                <span class="badge bg-primary-subtle text-primary small">Interactive</span>
            </div>
            <div class="list-group list-group-flush gap-1">
                <a href="#" class="hotkey-list-item d-flex justify-content-between align-items-center border-0" data-bs-toggle="modal" data-bs-target="#companySelectModal">
                    <span><kbd class="me-2">F2</kbd> Switch Company</span>
                    <span class="text-muted small">Select <i class="fa-solid fa-angle-right ms-1 opacity-50"></i></span>
                </a>
                <a href="#" class="hotkey-list-item d-flex justify-content-between align-items-center border-0" data-bs-toggle="modal" data-bs-target="#fySelectModal">
                    <span><kbd class="me-2">F3</kbd> Switch Financial Year</span>
                    <span class="text-muted small">Select <i class="fa-solid fa-angle-right ms-1 opacity-50"></i></span>
                </a>
                <a href="{{ route('vouchers.create', ['type' => 'CONTRA']) }}" class="hotkey-list-item d-flex justify-content-between align-items-center border-0">
                    <span><kbd class="me-2 text-info">F4</kbd> Contra Voucher</span>
                    <span class="text-muted small">Cash/Bank <i class="fa-solid fa-angle-right ms-1 opacity-50"></i></span>
                </a>
                <a href="{{ route('vouchers.create', ['type' => 'PAYMENT']) }}" class="hotkey-list-item d-flex justify-content-between align-items-center border-0">
                    <span><kbd class="me-2 text-danger">F5</kbd> Payment Voucher</span>
                    <span class="text-muted small">Outward <i class="fa-solid fa-angle-right ms-1 opacity-50"></i></span>
                </a>
                <a href="{{ route('vouchers.create', ['type' => 'RECEIPT']) }}" class="hotkey-list-item d-flex justify-content-between align-items-center border-0">
                    <span><kbd class="me-2 text-success">F6</kbd> Receipt Voucher</span>
                    <span class="text-muted small">Inward <i class="fa-solid fa-angle-right ms-1 opacity-50"></i></span>
                </a>
                <a href="{{ route('vouchers.create', ['type' => 'JOURNAL']) }}" class="hotkey-list-item d-flex justify-content-between align-items-center border-0">
                    <span><kbd class="me-2">F7</kbd> Journal Voucher</span>
                    <span class="text-muted small">Adjust <i class="fa-solid fa-angle-right ms-1 opacity-50"></i></span>
                </a>
                <a href="{{ route('sales.create') }}" class="hotkey-list-item d-flex justify-content-between align-items-center border-0">
                    <span><kbd class="me-2 text-primary">F8</kbd> Sales Invoice</span>
                    <span class="text-muted small">Billing <i class="fa-solid fa-angle-right ms-1 opacity-50"></i></span>
                </a>
                <a href="{{ route('purchases.create') }}" class="hotkey-list-item d-flex justify-content-between align-items-center border-0">
                    <span><kbd class="me-2 text-warning">F9</kbd> Purchase Invoice</span>
                    <span class="text-muted small">Inward <i class="fa-solid fa-angle-right ms-1 opacity-50"></i></span>
                </a>
                <a href="#" class="hotkey-list-item d-flex justify-content-between align-items-center border-0" data-bs-toggle="modal" data-bs-target="#spotlightSearchModal">
                    <span><kbd class="me-2">Ctrl+F</kbd> Spotlight Search</span>
                    <span class="text-muted small">Search <i class="fa-solid fa-angle-right ms-1 opacity-50"></i></span>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Sales vs Purchase Chart
    const ctxBar = document.getElementById('salesPurchaseChart');
    if (ctxBar) {
        new Chart(ctxBar, {
            type: 'bar',
            data: {
                labels: ['Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec', 'Jan', 'Feb', 'Mar'],
                datasets: [
                    {
                        label: 'Sales (₹)',
                        data: [15000, 22000, 31000, 28000, 35000, {{ $metrics['sales_total'] }}, 0, 0, 0, 0, 0, 0],
                        backgroundColor: 'rgba(79, 70, 229, 0.85)',
                        borderRadius: 6,
                    },
                    {
                        label: 'Purchases (₹)',
                        data: [12000, 18000, 25000, 20000, 29000, {{ $metrics['purchase_total'] }}, 0, 0, 0, 0, 0, 0],
                        backgroundColor: 'rgba(245, 158, 11, 0.85)',
                        borderRadius: 6,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top' }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(val) { return '₹ ' + val.toLocaleString(); }
                        }
                    }
                }
            }
        });
    }

    // 2. Top Products Chart
    const ctxPie = document.getElementById('topProductsChart');
    if (ctxPie) {
        new Chart(ctxPie, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($metrics['top_products']->pluck('name')) !!},
                datasets: [{
                    data: {!! json_encode($metrics['top_products']->pluck('current_stock')) !!},
                    backgroundColor: [
                        '#4f46e5',
                        '#06b6d4',
                        '#10b981',
                        '#f59e0b',
                        '#ec4899'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '72%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 10,
                            padding: 8,
                            font: { size: 11 }
                        }
                    }
                }
            }
        });
    }
});
</script>
@endpush
