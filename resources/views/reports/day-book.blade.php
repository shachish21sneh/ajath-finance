@extends('layouts.app')

@section('title', 'Day Book')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-3 no-print">
    <div>
        <h4 class="fw-bold mb-1"><i class="fa-solid fa-calendar-day text-primary me-2"></i> Day Book</h4>
        <p class="text-muted small mb-0">Daily chronological transaction register for <strong>{{ $company->name ?? '' }}</strong></p>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-outline-primary btn-sm btn-trigger-print" onclick="window.print()">
            <i class="fa-solid fa-print me-1"></i> Print Day Book (Ctrl+P)
        </button>
    </div>
</div>

<!-- Filter Bar -->
<div class="card card-modern p-3 mb-4 no-print">
    <form method="GET" action="{{ route('reports.day-book') }}" class="row g-2 align-items-end">
        <div class="col-md-3">
            <label class="form-label small fw-semibold">From Date</label>
            <input type="date" name="from_date" class="form-control form-control-sm" value="{{ $fromDate }}">
        </div>
        <div class="col-md-3">
            <label class="form-label small fw-semibold">To Date</label>
            <input type="date" name="to_date" class="form-control form-control-sm" value="{{ $toDate }}">
        </div>
        <div class="col-md-3">
            <label class="form-label small fw-semibold">Voucher Type Filter</label>
            <select name="voucher_type" class="form-select form-select-sm">
                <option value="">-- All Types --</option>
                <option value="CONTRA" {{ $voucherType === 'CONTRA' ? 'selected' : '' }}>Contra (F4)</option>
                <option value="PAYMENT" {{ $voucherType === 'PAYMENT' ? 'selected' : '' }}>Payment (F5)</option>
                <option value="RECEIPT" {{ $voucherType === 'RECEIPT' ? 'selected' : '' }}>Receipt (F6)</option>
                <option value="JOURNAL" {{ $voucherType === 'JOURNAL' ? 'selected' : '' }}>Journal (F7)</option>
                <option value="SALES" {{ $voucherType === 'SALES' ? 'selected' : '' }}>Sales (F8)</option>
                <option value="PURCHASE" {{ $voucherType === 'PURCHASE' ? 'selected' : '' }}>Purchase (F9)</option>
            </select>
        </div>
        <div class="col-md-3">
            <button type="submit" class="btn btn-primary btn-sm w-100 fw-semibold">
                <i class="fa-solid fa-filter me-1"></i> Apply Filter
            </button>
        </div>
    </form>
</div>

<!-- Day Book Sheet -->
<div class="card card-modern p-4 print-invoice-sheet">
    <div class="text-center mb-4">
        <h4 class="fw-bold mb-1">{{ $company->name ?? 'Company Name' }}</h4>
        <h6 class="text-uppercase fw-bold text-muted">Day Book Register</h6>
        <div class="small text-muted">Period: {{ date('d-M-Y', strtotime($fromDate)) }} to {{ date('d-M-Y', strtotime($toDate)) }}</div>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle mb-0">
            <thead class="table-light small text-uppercase">
                <tr>
                    <th style="width: 100px;">Date</th>
                    <th style="width: 130px;">Voucher #</th>
                    <th style="width: 100px;">Type</th>
                    <th>Particulars / Ledger</th>
                    <th class="text-end" style="width: 150px;">Debit Amount (₹)</th>
                    <th class="text-end" style="width: 150px;">Credit Amount (₹)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data['vouchers'] as $v)
                    <tr>
                        <td class="small">{{ $v->voucher_date->format('d-M-Y') }}</td>
                        <td class="fw-bold small">
                            <a href="{{ route('vouchers.show', $v->id) }}" class="text-decoration-none">
                                {{ $v->voucher_no }}
                            </a>
                        </td>
                        <td>
                            <span class="badge {{ $v->voucher_type->badgeClass() }}">{{ $v->voucher_type->value }}</span>
                        </td>
                        <td>
                            <div class="fw-semibold small">{{ $v->partyLedger->name ?? 'Multi-Ledger Journal' }}</div>
                            @if($v->narration)
                                <div class="text-muted small" style="font-size: 0.72rem;">{{ $v->narration }}</div>
                            @endif
                        </td>
                        <td class="text-end fw-semibold num-align text-danger">₹ {{ number_format($v->total_amount, 2) }}</td>
                        <td class="text-end fw-semibold num-align text-success">₹ {{ number_format($v->total_amount, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-5">No transactions recorded for the selected period.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot class="table-light fw-bold">
                <tr>
                    <td colspan="4" class="text-end text-uppercase">Period Total ({{ $data['count'] }} entries):</td>
                    <td class="text-end num-align text-danger">₹ {{ number_format($data['total_debit'], 2) }}</td>
                    <td class="text-end num-align text-success">₹ {{ number_format($data['total_credit'], 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection
