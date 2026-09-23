@extends('layouts.app')

@section('title', 'Profit & Loss Statement')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-3 no-print">
    <div>
        <h4 class="fw-bold mb-1"><i class="fa-solid fa-chart-line text-primary me-2"></i> Profit & Loss Statement</h4>
        <p class="text-muted small mb-0">Trading account & Income Statement for <strong>{{ $company->name ?? '' }}</strong></p>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-outline-primary btn-sm btn-trigger-print" onclick="window.print()">
            <i class="fa-solid fa-print me-1"></i> Print P&L (Ctrl+P)
        </button>
    </div>
</div>

<!-- Date Filter -->
<div class="card card-modern p-3 mb-4 no-print">
    <form method="GET" action="{{ route('reports.profit-loss') }}" class="row g-2 align-items-end">
        <div class="col-md-3">
            <label class="form-label small fw-semibold">From Date</label>
            <input type="date" name="from_date" class="form-control form-control-sm" value="{{ $fromDate }}">
        </div>
        <div class="col-md-3">
            <label class="form-label small fw-semibold">To Date</label>
            <input type="date" name="to_date" class="form-control form-control-sm" value="{{ $toDate }}">
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary btn-sm w-100 fw-semibold">Apply Period</button>
        </div>
    </form>
</div>

<div class="card card-modern p-4 print-invoice-sheet">
    <div class="text-center mb-4">
        <h4 class="fw-bold mb-1">{{ $company->name ?? '' }}</h4>
        <h6 class="text-uppercase fw-bold text-muted">Profit & Loss Account</h6>
        <div class="small text-muted">For the period {{ date('d-M-Y', strtotime($fromDate)) }} to {{ date('d-M-Y', strtotime($toDate)) }}</div>
    </div>

    <!-- Summary KPI Pills -->
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="p-3 bg-light rounded-3 d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small d-block">GROSS PROFIT (Trading)</span>
                    <span class="fs-4 fw-bold {{ $pnl['gross_profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                        ₹ {{ number_format($pnl['gross_profit'], 2) }}
                    </span>
                </div>
                <div class="fs-2 text-muted opacity-50"><i class="fa-solid fa-boxes-packing"></i></div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="p-3 bg-light rounded-3 d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small d-block">NET PROFIT / (LOSS)</span>
                    <span class="fs-4 fw-bold {{ $pnl['net_profit'] >= 0 ? 'text-primary' : 'text-danger' }}">
                        ₹ {{ number_format($pnl['net_profit'], 2) }}
                    </span>
                </div>
                <div class="fs-2 text-muted opacity-50"><i class="fa-solid fa-trophy"></i></div>
            </div>
        </div>
    </div>

    <!-- T-Format Accounting Table -->
    <div class="row g-4">
        <!-- Expenses Column (Debit Side) -->
        <div class="col-md-6">
            <div class="border rounded-3 p-3 h-100">
                <h6 class="fw-bold text-uppercase border-bottom pb-2 text-danger small">Expenses & Outflow</h6>
                <div class="small">
                    <!-- Direct Expenses -->
                    <div class="fw-bold text-muted my-2">Direct Expenses (Trading):</div>
                    @php $hasDirectExp = false; @endphp
                    @foreach($pnl['expense_rows'] as $r)
                        @if($r['affects_gross'])
                            @php $hasDirectExp = true; @endphp
                            <div class="d-flex justify-content-between py-1 border-bottom">
                                <span>{{ $r['ledger'] }}</span>
                                <span class="fw-semibold num-align">₹ {{ number_format($r['amount'], 2) }}</span>
                            </div>
                        @endif
                    @endforeach
                    @if(!$hasDirectExp) <div class="text-muted fst-italic py-1">No direct expenses</div> @endif

                    <!-- Indirect Expenses -->
                    <div class="fw-bold text-muted mt-3 mb-2">Indirect Expenses (Operating):</div>
                    @php $hasIndExp = false; @endphp
                    @foreach($pnl['expense_rows'] as $r)
                        @if(!$r['affects_gross'])
                            @php $hasIndExp = true; @endphp
                            <div class="d-flex justify-content-between py-1 border-bottom">
                                <span>{{ $r['ledger'] }}</span>
                                <span class="fw-semibold num-align">₹ {{ number_format($r['amount'], 2) }}</span>
                            </div>
                        @endif
                    @endforeach
                    @if(!$hasIndExp) <div class="text-muted fst-italic py-1">No indirect expenses</div> @endif
                </div>
            </div>
        </div>

        <!-- Incomes Column (Credit Side) -->
        <div class="col-md-6">
            <div class="border rounded-3 p-3 h-100">
                <h6 class="fw-bold text-uppercase border-bottom pb-2 text-success small">Incomes & Inflow</h6>
                <div class="small">
                    <!-- Direct Incomes -->
                    <div class="fw-bold text-muted my-2">Direct Incomes (Sales / Turnover):</div>
                    @php $hasDirectInc = false; @endphp
                    @foreach($pnl['income_rows'] as $r)
                        @if($r['affects_gross'])
                            @php $hasDirectInc = true; @endphp
                            <div class="d-flex justify-content-between py-1 border-bottom">
                                <span>{{ $r['ledger'] }}</span>
                                <span class="fw-semibold num-align">₹ {{ number_format($r['amount'], 2) }}</span>
                            </div>
                        @endif
                    @endforeach
                    @if(!$hasDirectInc) <div class="text-muted fst-italic py-1">No direct sales/incomes</div> @endif

                    <!-- Indirect Incomes -->
                    <div class="fw-bold text-muted mt-3 mb-2">Indirect Incomes:</div>
                    @php $hasIndInc = false; @endphp
                    @foreach($pnl['income_rows'] as $r)
                        @if(!$r['affects_gross'])
                            @php $hasIndInc = true; @endphp
                            <div class="d-flex justify-content-between py-1 border-bottom">
                                <span>{{ $r['ledger'] }}</span>
                                <span class="fw-semibold num-align">₹ {{ number_format($r['amount'], 2) }}</span>
                            </div>
                        @endif
                    @endforeach
                    @if(!$hasIndInc) <div class="text-muted fst-italic py-1">No indirect incomes</div> @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
