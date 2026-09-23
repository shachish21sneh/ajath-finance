@extends('layouts.app')

@section('title', 'Executive Dashboard')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-3">
    <div>
        <h4 class="fw-bold mb-1">Financial Overview & Dashboard</h4>
        <p class="text-muted small mb-0">Live accounting metrics for <strong>{{ $company->name ?? 'Default Company' }}</strong> ({{ $currentFinancialYear->title ?? 'Current FY' }})</p>
    </div>
    <div class="d-flex gap-2">
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
    <div class="col-sm-6 col-xl-3">
        <div class="card card-modern stat-card border-start border-primary border-4">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="stat-label">Total Sales (Turnover)</div>
                    <div class="stat-value text-primary">₹ {{ number_format($metrics['sales_total'], 2) }}</div>
                    <span class="badge bg-primary-subtle text-primary small"><i class="fa-solid fa-arrow-trend-up me-1"></i> Active FY</span>
                </div>
                <div class="stat-icon bg-primary-subtle text-primary">
                    <i class="fa-solid fa-file-invoice-dollar"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Purchases -->
    <div class="col-sm-6 col-xl-3">
        <div class="card card-modern stat-card border-start border-warning border-4">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="stat-label">Total Purchases</div>
                    <div class="stat-value text-warning-emphasis">₹ {{ number_format($metrics['purchase_total'], 2) }}</div>
                    <span class="badge bg-warning-subtle text-warning-emphasis small"><i class="fa-solid fa-cart-shopping me-1"></i> Direct Procurement</span>
                </div>
                <div class="stat-icon bg-warning-subtle text-warning">
                    <i class="fa-solid fa-cart-flatbed"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Outstanding Receivables -->
    <div class="col-sm-6 col-xl-3">
        <div class="card card-modern stat-card border-start border-danger border-4">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="stat-label">Receivables (Debtors)</div>
                    <div class="stat-value text-danger">₹ {{ number_format($metrics['receivables'], 2) }}</div>
                    <span class="badge bg-danger-subtle text-danger small"><i class="fa-solid fa-clock me-1"></i> Due From Customers</span>
                </div>
                <div class="stat-icon bg-danger-subtle text-danger">
                    <i class="fa-solid fa-hand-holding-dollar"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Liquid Cash & Bank -->
    <div class="col-sm-6 col-xl-3">
        <div class="card card-modern stat-card border-start border-success border-4">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="stat-label">Liquid Bank & Cash</div>
                    <div class="stat-value text-success">₹ {{ number_format($metrics['cash_balance'] + $metrics['bank_balance'], 2) }}</div>
                    <span class="badge bg-success-subtle text-success small">Bank: ₹ {{ number_format($metrics['bank_balance'], 0) }} | Cash: ₹ {{ number_format($metrics['cash_balance'], 0) }}</span>
                </div>
                <div class="stat-icon bg-success-subtle text-success">
                    <i class="fa-solid fa-building-columns"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Interactive Chart.js Visualizations -->
<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="card card-modern p-4">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h6 class="fw-bold mb-0"><i class="fa-solid fa-chart-column me-2 text-primary"></i> Monthly Sales & Purchase Flow</h6>
                <span class="badge bg-light text-muted border">FY 2025-26</span>
            </div>
            <div style="height: 280px;">
                <canvas id="salesPurchaseChart"></canvas>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card card-modern p-4">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h6 class="fw-bold mb-0"><i class="fa-solid fa-chart-pie me-2 text-primary"></i> Top Products by Stock</h6>
                <span class="badge bg-light text-muted border">Live Stock</span>
            </div>
            <div style="height: 280px; position: relative;">
                <canvas id="topProductsChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Recent Transactions & Quick Shortcut Guide -->
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card card-modern p-4">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h6 class="fw-bold mb-0"><i class="fa-solid fa-clock-rotate-left me-2 text-primary"></i> Recent Voucher Transactions</h6>
                <a href="{{ route('vouchers.index') }}" class="btn btn-sm btn-link text-decoration-none">View All Vouchers <i class="fa-solid fa-arrow-right ms-1"></i></a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr class="small text-muted text-uppercase">
                            <th>Type</th>
                            <th>Voucher #</th>
                            <th>Date</th>
                            <th>Particulars / Ledger</th>
                            <th class="text-end">Amount</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($metrics['recent_vouchers'] as $v)
                            <tr>
                                <td>
                                    <span class="badge {{ $v->voucher_type->badgeClass() }}">
                                        {{ $v->voucher_type->value }}
                                    </span>
                                </td>
                                <td class="fw-semibold small">{{ $v->voucher_no }}</td>
                                <td class="small text-muted">{{ $v->voucher_date->format('d-M-Y') }}</td>
                                <td class="small">
                                    <div class="fw-medium">{{ $v->partyLedger->name ?? 'Multi-Ledger Entry' }}</div>
                                    @if($v->narration)
                                        <div class="text-muted text-truncate" style="max-width: 200px; font-size: 0.75rem;">{{ $v->narration }}</div>
                                    @endif
                                </td>
                                <td class="text-end fw-bold num-align">₹ {{ number_format($v->total_amount, 2) }}</td>
                                <td class="text-center">
                                    <a href="{{ route('vouchers.show', $v->id) }}" class="btn btn-sm btn-light border py-0 px-2" title="View details">
                                        <i class="fa-solid fa-eye text-muted"></i>
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

    <div class="col-lg-4">
        <div class="card card-modern p-4">
            <h6 class="fw-bold mb-3"><i class="fa-solid fa-keyboard me-2 text-primary"></i> Productivity Hotkeys</h6>
            <div class="list-group list-group-flush small">
                <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 border-0">
                    <span><kbd class="me-2">F2</kbd> Switch Company</span>
                    <span class="text-muted">Instant Popup</span>
                </div>
                <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 border-0">
                    <span><kbd class="me-2">F3</kbd> Switch Financial Year</span>
                    <span class="text-muted">Instant Popup</span>
                </div>
                <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 border-0">
                    <span><kbd class="me-2 text-info">F4</kbd> Contra Voucher</span>
                    <span class="text-muted">Cash & Bank</span>
                </div>
                <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 border-0">
                    <span><kbd class="me-2 text-danger">F5</kbd> Payment Voucher</span>
                    <span class="text-muted">Outward Cash/Bank</span>
                </div>
                <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 border-0">
                    <span><kbd class="me-2 text-success">F6</kbd> Receipt Voucher</span>
                    <span class="text-muted">Inward Money</span>
                </div>
                <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 border-0">
                    <span><kbd class="me-2">F7</kbd> Journal Voucher</span>
                    <span class="text-muted">Adjustment Entry</span>
                </div>
                <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 border-0">
                    <span><kbd class="me-2 text-primary">F8</kbd> Sales Invoice</span>
                    <span class="text-muted">Tax & POS Bills</span>
                </div>
                <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 border-0">
                    <span><kbd class="me-2 text-warning">F9</kbd> Purchase Invoice</span>
                    <span class="text-muted">Vendor Bills</span>
                </div>
                <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 border-0">
                    <span><kbd class="me-2">Ctrl+F</kbd> Spotlight Search</span>
                    <span class="text-muted">Search All Records</span>
                </div>
                <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 border-0">
                    <span><kbd class="me-2">Ctrl+S</kbd> Save Active Voucher</span>
                    <span class="text-muted">Direct Submission</span>
                </div>
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
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    }
});
</script>
@endpush
