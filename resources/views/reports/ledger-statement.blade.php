@extends('layouts.app')

@section('title', 'Ledger Statement - ' . $ledger->name)

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-3 no-print">
    <div class="d-flex align-items-center gap-3">
        <a href="{{ route('ledgers.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fa-solid fa-arrow-left me-1"></i> Back to Accounts
        </a>
        <div>
            <h4 class="fw-bold mb-0">{{ $ledger->name }}</h4>
            <span class="text-muted small">Account Statement &bull; {{ $ledger->group->name ?? 'General Group' }}</span>
        </div>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-outline-primary btn-sm btn-trigger-print" onclick="window.print()">
            <i class="fa-solid fa-print me-1"></i> Print Statement (Ctrl+P)
        </button>
    </div>
</div>

<!-- Date Filter -->
<div class="card card-modern p-3 mb-4 no-print">
    <form method="GET" action="{{ route('ledgers.statement', $ledger->id) }}" class="row g-2 align-items-end">
        <div class="col-md-4">
            <label class="form-label small fw-semibold">From Date</label>
            <input type="date" name="from_date" class="form-control form-control-sm" value="{{ $fromDate }}">
        </div>
        <div class="col-md-4">
            <label class="form-label small fw-semibold">To Date</label>
            <input type="date" name="to_date" class="form-control form-control-sm" value="{{ $toDate }}">
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary btn-sm w-100 fw-semibold">Update Statement</button>
        </div>
    </form>
</div>

<!-- Statement Print Sheet -->
<div class="card card-modern p-4 p-md-5 print-invoice-sheet">
    <div class="row align-items-start border-bottom pb-4 mb-4">
        <div class="col-7">
            <h4 class="fw-bold text-main mb-1">{{ $company->name ?? '' }}</h4>
            <div class="text-muted small">{{ $company->address }}, {{ $company->city }}</div>
            <div class="text-muted small">GSTIN: {{ $company->gstin ?: 'N/A' }}</div>
        </div>
        <div class="col-5 text-end">
            <h5 class="fw-bold mb-1 text-primary">{{ $ledger->name }}</h5>
            <div class="text-muted small">Classification: <strong>{{ strtoupper($ledger->party_type->value) }}</strong></div>
            <div class="text-muted small">Period: <strong>{{ date('d-M-Y', strtotime($fromDate)) }} to {{ date('d-M-Y', strtotime($toDate)) }}</strong></div>
            @if($ledger->gstin)
                <div class="text-muted small">Party GSTIN: {{ $ledger->gstin }}</div>
            @endif
        </div>
    </div>

    <!-- Opening Balance Banner -->
    <div class="d-flex justify-content-between align-items-center bg-light p-3 rounded-3 mb-3 small">
        <span class="fw-bold text-uppercase">Opening Balance (as of {{ date('d-M-Y', strtotime($fromDate)) }}):</span>
        <span class="fs-6 fw-bold num-align {{ $statement['opening_balance'] >= 0 ? 'text-primary' : 'text-danger' }}">
            ₹ {{ number_format(abs($statement['opening_balance']), 2) }} {{ $statement['opening_type'] }}
        </span>
    </div>

    <div class="table-responsive mb-4">
        <table class="table table-bordered table-hover align-middle mb-0 small">
            <thead class="table-light text-uppercase">
                <tr>
                    <th style="width: 100px;">Date</th>
                    <th style="width: 130px;">Voucher #</th>
                    <th>Particulars / Narration</th>
                    <th class="text-end" style="width: 130px;">Debit (₹)</th>
                    <th class="text-end" style="width: 130px;">Credit (₹)</th>
                    <th class="text-end" style="width: 150px;">Running Balance</th>
                </tr>
            </thead>
            <tbody>
                @forelse($statement['entries'] as $item)
                    <tr>
                        <td>{{ $item['entry']->entry_date->format('d-M-Y') }}</td>
                        <td class="fw-bold">
                            <a href="{{ route('vouchers.show', $item['entry']->voucher_id) }}" class="text-decoration-none">
                                {{ $item['entry']->voucher->voucher_no ?? '-' }}
                            </a>
                        </td>
                        <td>
                            <div>{{ $item['entry']->narration ?: 'Voucher entry' }}</div>
                        </td>
                        <td class="text-end fw-semibold num-align {{ $item['debit'] > 0 ? 'text-danger' : 'text-muted' }}">
                            {{ $item['debit'] > 0 ? '₹ ' . number_format($item['debit'], 2) : '-' }}
                        </td>
                        <td class="text-end fw-semibold num-align {{ $item['credit'] > 0 ? 'text-success' : 'text-muted' }}">
                            {{ $item['credit'] > 0 ? '₹ ' . number_format($item['credit'], 2) : '-' }}
                        </td>
                        <td class="text-end fw-bold num-align {{ $item['running_balance'] >= 0 ? 'text-primary' : 'text-danger' }}">
                            ₹ {{ number_format(abs($item['running_balance']), 2) }} {{ $item['running_type'] }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">No entries recorded in this period.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot class="table-light fw-bold">
                <tr>
                    <td colspan="3" class="text-end text-uppercase">Period Transactions Total:</td>
                    <td class="text-end text-danger num-align">₹ {{ number_format($statement['total_debit'], 2) }}</td>
                    <td class="text-end text-success num-align">₹ {{ number_format($statement['total_credit'], 2) }}</td>
                    <td class="text-end text-primary num-align">
                        ₹ {{ number_format(abs($statement['closing_balance']), 2) }} {{ $statement['closing_type'] }}
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Closing Balance Banner -->
    <div class="d-flex justify-content-between align-items-center border p-3 rounded-3 bg-white">
        <span class="fw-bold text-uppercase">Net Closing Balance (as of {{ date('d-M-Y', strtotime($toDate)) }}):</span>
        <span class="fs-5 fw-bold num-align {{ $statement['closing_balance'] >= 0 ? 'text-primary' : 'text-danger' }}">
            ₹ {{ number_format(abs($statement['closing_balance']), 2) }} {{ $statement['closing_type'] }}
        </span>
    </div>
</div>
@endsection
