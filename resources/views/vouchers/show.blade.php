@extends('layouts.app')

@section('title', 'Voucher ' . $voucher->voucher_no)

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4 no-print">
    <div class="d-flex align-items-center gap-3">
        <a href="{{ route('vouchers.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fa-solid fa-arrow-left me-1"></i> Back
        </a>
        <h4 class="fw-bold mb-0">Voucher Details: {{ $voucher->voucher_no }}</h4>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-outline-primary btn-sm btn-trigger-print" onclick="window.print()">
            <i class="fa-solid fa-print me-1"></i> Print Voucher (Ctrl+P)
        </button>
        <a href="{{ route('vouchers.create', ['type' => $voucher->voucher_type->value]) }}" class="btn btn-primary btn-sm">
            <i class="fa-solid fa-plus me-1"></i> New {{ $voucher->voucher_type->value }}
        </a>
    </div>
</div>

<div class="card card-modern p-4 p-md-5 print-invoice-sheet mx-auto" style="max-width: 900px;">
    <!-- Company Header -->
    <div class="row align-items-center border-bottom pb-4 mb-4">
        <div class="col-8">
            <h4 class="fw-bold text-main mb-1">{{ $voucher->company->name }}</h4>
            <div class="text-muted small">{{ $voucher->company->legal_name }}</div>
            <div class="text-muted small">{{ $voucher->company->address }}, {{ $voucher->company->city }}, {{ $voucher->company->state }} - {{ $voucher->company->pincode }}</div>
            <div class="text-muted small"><strong>GSTIN:</strong> {{ $voucher->company->gstin ?: 'N/A' }} | <strong>PAN:</strong> {{ $voucher->company->pan ?: 'N/A' }}</div>
        </div>
        <div class="col-4 text-end">
            <span class="badge {{ $voucher->voucher_type->badgeClass() }} fs-6 px-3 py-2 mb-2">
                {{ $voucher->voucher_type->label() }}
            </span>
            <div class="fw-bold fs-5 text-main">{{ $voucher->voucher_no }}</div>
            <div class="text-muted small">Date: <strong>{{ $voucher->voucher_date->format('d-M-Y') }}</strong></div>
        </div>
    </div>

    <!-- Voucher Details Strip -->
    <div class="row g-3 p-3 bg-light rounded-3 mb-4 small">
        <div class="col-sm-4">
            <span class="text-muted d-block">Payment Mode:</span>
            <span class="fw-bold text-uppercase">{{ $voucher->payment_mode }}</span>
        </div>
        <div class="col-sm-4">
            <span class="text-muted d-block">Reference Number:</span>
            <span class="fw-semibold">{{ $voucher->reference_no ?: 'None' }}</span>
        </div>
        <div class="col-sm-4">
            <span class="text-muted d-block">Primary Party / Account:</span>
            <span class="fw-semibold">{{ $voucher->partyLedger->name ?? 'Multi-Ledger Journal' }}</span>
        </div>
        @if($voucher->cheque_no)
            <div class="col-sm-6">
                <span class="text-muted d-block">Cheque Number:</span>
                <span class="fw-semibold">{{ $voucher->cheque_no }} (Date: {{ $voucher->cheque_date?->format('d-M-Y') }})</span>
            </div>
        @endif
    </div>

    <!-- Multi-Ledger Entries Table -->
    <div class="table-responsive mb-4">
        <table class="table table-bordered align-middle">
            <thead class="table-light small text-uppercase">
                <tr>
                    <th style="width: 80px;" class="text-center">Dr / Cr</th>
                    <th>Account Particulars</th>
                    <th style="width: 180px;" class="text-end">Debit (₹)</th>
                    <th style="width: 180px;" class="text-end">Credit (₹)</th>
                </tr>
            </thead>
            <tbody>
                @php $totDr = 0; $totCr = 0; @endphp
                @foreach($voucher->items as $item)
                    @php
                        $isDr = $item->entry_type->value === 'debit';
                        if ($isDr) $totDr += (float) $item->amount; else $totCr += (float) $item->amount;
                    @endphp
                    <tr>
                        <td class="text-center fw-bold {{ $isDr ? 'text-danger' : 'text-success' }}">
                            {{ $isDr ? 'Dr' : 'Cr' }}
                        </td>
                        <td>
                            <div class="fw-bold text-main">{{ $item->ledger->name }}</div>
                            <div class="text-muted small">{{ $item->ledger->group->name ?? '' }}</div>
                            @if($item->narration)
                                <div class="text-muted small fst-italic mt-1"><i class="fa-solid fa-angle-right me-1"></i> {{ $item->narration }}</div>
                            @endif
                        </td>
                        <td class="text-end fw-semibold num-align {{ $isDr ? 'text-danger' : '' }}">
                            {{ $isDr ? '₹ ' . number_format($item->amount, 2) : '-' }}
                        </td>
                        <td class="text-end fw-semibold num-align {{ !$isDr ? 'text-success' : '' }}">
                            {{ !$isDr ? '₹ ' . number_format($item->amount, 2) : '-' }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot class="table-light fw-bold">
                <tr>
                    <td colspan="2" class="text-end text-uppercase">Total</td>
                    <td class="text-end text-danger num-align">₹ {{ number_format($totDr, 2) }}</td>
                    <td class="text-end text-success num-align">₹ {{ number_format($totCr, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Amount in Words & Narration -->
    <div class="p-3 border rounded-3 bg-light mb-5">
        <div class="small mb-2">
            <span class="text-muted">Amount in Words:</span>
            <strong class="text-main">{{ \App\Helpers\AccountingHelper::amountToWords($voucher->total_amount) }}</strong>
        </div>
        @if($voucher->narration)
            <div class="small">
                <span class="text-muted">Master Narration:</span>
                <span class="text-main fst-italic">{{ $voucher->narration }}</span>
            </div>
        @endif
    </div>

    <!-- Signatures -->
    <div class="row pt-5 mt-4 text-center">
        <div class="col-4">
            <div class="border-top pt-2 small text-muted">
                Prepared By ({{ $voucher->creator->name ?? 'Accountant' }})
            </div>
        </div>
        <div class="col-4">
            <div class="border-top pt-2 small text-muted">
                Verified By
            </div>
        </div>
        <div class="col-4">
            <div class="border-top pt-2 small text-muted">
                Authorized Signatory
            </div>
        </div>
    </div>
</div>
@endsection
