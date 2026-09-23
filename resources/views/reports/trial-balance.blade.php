@extends('layouts.app')

@section('title', 'Trial Balance')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-3 no-print">
    <div>
        <h4 class="fw-bold mb-1"><i class="fa-solid fa-scale-balanced text-primary me-2"></i> Trial Balance</h4>
        <p class="text-muted small mb-0">Verifies mathematical equality of all debit and credit balances in the general ledger</p>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-outline-primary btn-sm btn-trigger-print" onclick="window.print()">
            <i class="fa-solid fa-print me-1"></i> Print Trial Balance (Ctrl+P)
        </button>
    </div>
</div>

<!-- As of Date Filter -->
<div class="card card-modern p-3 mb-4 no-print">
    <form method="GET" action="{{ route('reports.trial-balance') }}" class="row g-2 align-items-end">
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
        <h6 class="text-uppercase fw-bold text-muted">Trial Balance Statement</h6>
        <div class="small text-muted">As of Date: <strong>{{ date('d-M-Y', strtotime($asOfDate)) }}</strong></div>
    </div>

    <!-- Status Banner -->
    <div class="mb-4">
        @if($tb['is_matched'])
            <div class="alert alert-success d-flex align-items-center justify-content-between mb-0 py-2 border-0">
                <div><i class="fa-solid fa-circle-check me-2"></i> <strong>Trial Balance is perfectly balanced!</strong> Total Debits match Total Credits.</div>
                <span class="badge bg-success">Difference: ₹ 0.00</span>
            </div>
        @else
            <div class="alert alert-danger d-flex align-items-center justify-content-between mb-0 py-2 border-0">
                <div><i class="fa-solid fa-triangle-exclamation me-2"></i> <strong>Discrepancy Detected!</strong> Debits and Credits do not match.</div>
                <span class="badge bg-danger">Diff: ₹ {{ number_format($tb['difference'], 2) }}</span>
            </div>
        @endif
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle mb-0">
            <thead class="table-light small text-uppercase">
                <tr>
                    <th>Ledger Account</th>
                    <th>Master Group</th>
                    <th>Nature</th>
                    <th class="text-end" style="width: 180px;">Debit (₹)</th>
                    <th class="text-end" style="width: 180px;">Credit (₹)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tb['rows'] as $row)
                    <tr>
                        <td>
                            <a href="{{ route('ledgers.statement', $row['ledger_id']) }}" class="fw-bold text-decoration-none text-main">
                                {{ $row['ledger_name'] }}
                            </a>
                        </td>
                        <td class="small">{{ $row['group_name'] }}</td>
                        <td>
                            <span class="badge bg-light text-muted border">{{ $row['nature'] }}</span>
                        </td>
                        <td class="text-end fw-semibold num-align {{ $row['debit'] > 0 ? 'text-danger' : 'text-muted' }}">
                            {{ $row['debit'] > 0 ? '₹ ' . number_format($row['debit'], 2) : '-' }}
                        </td>
                        <td class="text-end fw-semibold num-align {{ $row['credit'] > 0 ? 'text-success' : 'text-muted' }}">
                            {{ $row['credit'] > 0 ? '₹ ' . number_format($row['credit'], 2) : '-' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-5">No ledger balances recorded as of this date.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot class="table-light fw-bold fs-6">
                <tr>
                    <td colspan="3" class="text-end text-uppercase">Trial Balance Total:</td>
                    <td class="text-end text-danger num-align">₹ {{ number_format($tb['total_debit'], 2) }}</td>
                    <td class="text-end text-success num-align">₹ {{ number_format($tb['total_credit'], 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection
