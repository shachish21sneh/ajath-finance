@extends('layouts.app')

@section('title', 'Balance Sheet')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-3 no-print">
    <div>
        <h4 class="fw-bold mb-1"><i class="fa-solid fa-vault text-primary me-2"></i> Balance Sheet Statement</h4>
        <p class="text-muted small mb-0">Financial position (Assets vs Liabilities & Capital) for <strong>{{ $company->name ?? '' }}</strong></p>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-outline-primary btn-sm btn-trigger-print" onclick="window.print()">
            <i class="fa-solid fa-print me-1"></i> Print Balance Sheet (Ctrl+P)
        </button>
    </div>
</div>

<div class="card card-modern p-3 mb-4 no-print">
    <form method="GET" action="{{ route('reports.balance-sheet') }}" class="row g-2 align-items-end">
        <div class="col-md-4">
            <label class="form-label small fw-semibold">As of Date</label>
            <input type="date" name="as_of_date" class="form-control form-control-sm" value="{{ $asOfDate }}">
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary btn-sm w-100 fw-semibold">Generate</button>
        </div>
    </form>
</div>

<div class="card card-modern p-4 print-invoice-sheet">
    <div class="text-center mb-4">
        <h4 class="fw-bold mb-1">{{ $company->name ?? '' }}</h4>
        <h6 class="text-uppercase fw-bold text-muted">Balance Sheet</h6>
        <div class="small text-muted">As of <strong>{{ date('d-M-Y', strtotime($asOfDate)) }}</strong></div>
    </div>

    <!-- Balance Status Banner -->
    <div class="mb-4">
        @if($bs['is_balanced'])
            <div class="alert alert-success d-flex align-items-center justify-content-between mb-0 py-2 border-0">
                <div><i class="fa-solid fa-circle-check me-2"></i> <strong>Balance Sheet is Balanced!</strong> Total Assets equal Total Liabilities & Equity.</div>
                <span class="badge bg-success">Net Profit: ₹ {{ number_format($bs['net_profit'], 2) }}</span>
            </div>
        @else
            <div class="alert alert-warning d-flex align-items-center justify-content-between mb-0 py-2 border-0">
                <div><i class="fa-solid fa-circle-info me-2"></i> Unadjusted Difference: ₹ {{ number_format($bs['difference'], 2) }}</div>
            </div>
        @endif
    </div>

    <!-- T-Format Balance Sheet Layout -->
    <div class="row g-4">
        <!-- Liabilities Column (Left) -->
        <div class="col-md-6">
            <div class="border rounded-3 p-3 h-100 d-flex flex-column justify-content-between">
                <div>
                    <h6 class="fw-bold text-uppercase border-bottom pb-2 text-warning-emphasis small">Liabilities & Capital</h6>
                    <div class="small">
                        @forelse($bs['liabilities'] as $l)
                            <div class="d-flex justify-content-between py-1 border-bottom">
                                <div>
                                    <span class="fw-medium">{{ $l['ledger'] }}</span>
                                    <span class="text-muted small">({{ $l['group'] }})</span>
                                </div>
                                <span class="fw-semibold num-align">₹ {{ number_format($l['amount'], 2) }}</span>
                            </div>
                        @empty
                            <div class="text-muted py-2 fst-italic">No external liabilities</div>
                        @endforelse

                        <!-- Net Profit Injected -->
                        <div class="d-flex justify-content-between py-2 border-bottom text-primary fw-bold bg-light px-2 rounded mt-2">
                            <span>Profit & Loss A/c (Net Profit):</span>
                            <span class="num-align">₹ {{ number_format($bs['net_profit'], 2) }}</span>
                        </div>
                    </div>
                </div>

                <div class="border-top pt-2 mt-4 d-flex justify-content-between fw-bold fs-6">
                    <span class="text-uppercase">Total Liabilities:</span>
                    <span class="text-warning-emphasis num-align">₹ {{ number_format($bs['total_liabilities_with_profit'], 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Assets Column (Right) -->
        <div class="col-md-6">
            <div class="border rounded-3 p-3 h-100 d-flex flex-column justify-content-between">
                <div>
                    <h6 class="fw-bold text-uppercase border-bottom pb-2 text-primary small">Assets (Current & Fixed)</h6>
                    <div class="small">
                        @forelse($bs['assets'] as $a)
                            <div class="d-flex justify-content-between py-1 border-bottom">
                                <div>
                                    <span class="fw-medium">{{ $a['ledger'] }}</span>
                                    <span class="text-muted small">({{ $a['group'] }})</span>
                                </div>
                                <span class="fw-semibold num-align">₹ {{ number_format($a['amount'], 2) }}</span>
                            </div>
                        @empty
                            <div class="text-muted py-2 fst-italic">No assets on record</div>
                        @endforelse
                    </div>
                </div>

                <div class="border-top pt-2 mt-4 d-flex justify-content-between fw-bold fs-6">
                    <span class="text-uppercase">Total Assets:</span>
                    <span class="text-primary num-align">₹ {{ number_format($bs['total_assets'], 2) }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
