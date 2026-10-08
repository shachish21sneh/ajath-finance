@extends('layouts.app')

@section('title', 'Purchase Bill ' . $purchase->bill_no)

@push('styles')
<style>
@media print {
    @page {
        size: A4 portrait;
        margin: 8mm;
    }
    body {
        background: #ffffff !important;
        color: #0f172a !important;
        font-size: 11px !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    .no-print, header, footer, .sidebar, .sidebar-backdrop, .quick-shortcuts-bar, .btn-trigger-print {
        display: none !important;
    }
    .app-main, main {
        margin: 0 !important;
        padding: 0 !important;
        width: 100% !important;
    }
    .print-invoice-sheet {
        box-shadow: none !important;
        border: none !important;
        padding: 0 !important;
        max-width: 100% !important;
        width: 100% !important;
        margin: 0 !important;
    }
}
</style>
@endpush

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4 no-print">
    <div class="d-flex align-items-center gap-3">
        <a href="{{ route('purchases.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fa-solid fa-arrow-left me-1"></i> Back to Bills
        </a>
        <h4 class="fw-bold mb-0">Vendor Bill: {{ $purchase->bill_no }}</h4>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('purchases.edit', $purchase->id) }}" class="btn btn-warning btn-sm fw-semibold">
            <i class="fa-solid fa-pen-to-square me-1"></i> Edit Bill
        </a>
        <button type="button" class="btn btn-outline-primary btn-sm btn-trigger-print" onclick="window.print()">
            <i class="fa-solid fa-print me-1"></i> Print Bill (Ctrl+P)
        </button>
        <a href="{{ route('purchases.create') }}" class="btn btn-primary btn-sm">
            <i class="fa-solid fa-plus me-1"></i> New Purchase (F9)
        </a>
    </div>
</div>

<!-- Payment & Vendor Notes (View Only - Excluded from Print Bill) -->
<div class="card card-modern p-4 mb-4 mx-auto no-print shadow-sm" style="max-width: 900px;">
    <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
        <h6 class="fw-bold mb-0 text-main d-flex align-items-center gap-2">
            <i class="fa-solid fa-receipt text-primary"></i> Payment & Vendor Notes
        </h6>
        <span class="badge bg-light border text-muted small fw-normal">
            <i class="fa-solid fa-eye-slash text-secondary me-1"></i> Screen view only &bull; Not in print bill
        </span>
    </div>
    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label small fw-semibold text-muted mb-1">Amount Paid (₹)</label>
            <div class="form-control bg-light fw-bold text-success">
                ₹ {{ number_format((float)($purchase->paid_amount ?? 0), 2) }}
            </div>
            @if((float)($purchase->due_amount ?? 0) > 0)
                <div class="small text-danger mt-1">
                    <i class="fa-solid fa-circle-exclamation me-1"></i> Balance Payable: <strong>₹ {{ number_format((float)$purchase->due_amount, 2) }}</strong>
                </div>
            @endif
        </div>
        <div class="col-md-4">
            <label class="form-label small fw-semibold text-muted mb-1">Payment Status</label>
            <div class="form-control bg-light">
                @php
                    $statusBadge = match($purchase->payment_status) {
                        'paid' => 'bg-success text-white',
                        'partial' => 'bg-warning text-dark',
                        default => 'bg-danger text-white',
                    };
                @endphp
                <span class="badge {{ $statusBadge }} px-2.5 py-1 text-uppercase fw-semibold">{{ $purchase->payment_status }}</span>
            </div>
        </div>
        <div class="col-md-4">
            <label class="form-label small fw-semibold text-muted mb-1">Purchase Notes / Remarks</label>
            <div class="form-control bg-light text-muted" style="min-height: 38px;">
                {{ $purchase->notes ?: '—' }}
            </div>
        </div>
    </div>
</div>

<div class="card card-modern p-4 p-md-5 print-invoice-sheet mx-auto shadow-sm" style="max-width: 900px;">
    <!-- Header -->
    <div class="row align-items-start border-bottom pb-4 mb-4">
        <div class="col-7">
            <span class="badge bg-warning-subtle text-warning-emphasis fs-6 px-3 py-1 mb-2">Vendor Purchase Bill</span>
            <h4 class="fw-bold text-main mb-1">{{ $purchase->supplier->name }}</h4>
            <div class="small text-muted">{{ $purchase->supplier->address ?: 'Supplier address' }}</div>
            <div class="small"><strong>GSTIN:</strong> {{ $purchase->supplier->gstin ?: 'Unregistered' }} | <strong>PAN:</strong> {{ $purchase->supplier->pan ?: 'N/A' }}</div>
            <div class="small"><strong>State:</strong> {{ $purchase->supplier->state }} ({{ $purchase->supplier->state_code }})</div>
        </div>
        <div class="col-5 text-end small">
            <table class="table table-sm table-borderless text-end mb-0">
                <tr>
                    <td class="text-muted py-0">Bill / Inv No:</td>
                    <td class="fw-bold py-0">{{ $purchase->bill_no }}</td>
                </tr>
                <tr>
                    <td class="text-muted py-0">Dated:</td>
                    <td class="fw-bold py-0">{{ $purchase->bill_date->format('d-M-Y') }}</td>
                </tr>
                <tr>
                    <td class="text-muted py-0">Due Date:</td>
                    <td class="py-0">{{ $purchase->due_date ? $purchase->due_date->format('d-M-Y') : 'On Receipt' }}</td>
                </tr>
                <tr>
                    <td class="text-muted py-0">Consignee:</td>
                    <td class="py-0">{{ $purchase->company->name }}</td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Items Table -->
    <div class="table-responsive mb-4">
        <table class="table table-bordered align-middle small mb-0">
            <thead class="table-light text-uppercase">
                <tr>
                    <th>Item Description</th>
                    <th style="width: 90px;" class="text-center">HSN</th>
                    <th style="width: 85px;" class="text-end">Qty</th>
                    <th style="width: 100px;" class="text-end">Rate (₹)</th>
                    <th style="width: 110px;" class="text-end">Taxable (₹)</th>
                    <th style="width: 70px;" class="text-center">GST %</th>
                    <th style="width: 120px;" class="text-end">Total (₹)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($purchase->items as $item)
                    <tr>
                        <td>
                            @php
                                $productName = $item->product ? $item->product->name : null;
                                $desc = trim((string)($item->description ?? ''));
                                $title = $productName ?: ($desc ?: 'Item');
                                $hasSubtitle = $productName && $desc !== '' && $desc !== $productName;
                            @endphp
                            <div class="fw-bold text-main" style="white-space: pre-line;">{{ $title }}</div>
                            @if($hasSubtitle)
                                <div class="text-muted fst-italic mt-0.5" style="font-size: 0.8125rem; white-space: pre-line;">{!! nl2br(e($desc)) !!}</div>
                            @endif
                        </td>
                        <td class="text-center text-muted">{{ $item->hsn_code ?: '-' }}</td>
                        <td class="text-end fw-semibold text-nowrap">
                            @php
                                $qtyFormatted = (float)$item->quantity == (int)$item->quantity ? number_format($item->quantity, 0) : number_format($item->quantity, 2);
                                $unitSymbol = $item->product?->unit?->symbol ?: ($item->product?->unit?->name ?: '');
                            @endphp
                            <span>{{ $qtyFormatted }}</span>@if($unitSymbol)<span class="text-uppercase ms-1" style="font-size: 0.85em; font-weight: 600;">{{ $unitSymbol }}</span>@endif
                        </td>
                        <td class="text-end num-align">₹ {{ number_format($item->unit_price, 2) }}</td>
                        <td class="text-end num-align fw-semibold">₹ {{ number_format($item->taxable_amount, 2) }}</td>
                        <td class="text-center">{{ (float)$item->gst_rate }}%</td>
                        <td class="text-end num-align fw-bold">₹ {{ number_format($item->total_amount, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Summary Box -->
    <div class="row justify-content-end mb-4">
        <div class="col-md-5">
            <div class="p-3 bg-light rounded-3 small">
                <div class="d-flex justify-content-between py-1">
                    <span class="text-muted">Taxable Value:</span>
                    <span class="fw-semibold num-align">₹ {{ number_format($purchase->taxable_amount, 2) }}</span>
                </div>
                <div class="d-flex justify-content-between py-1 text-warning-emphasis">
                    <span>Input Tax Credit (GST):</span>
                    <span class="fw-semibold num-align">₹ {{ number_format($purchase->cgst_amount + $purchase->sgst_amount + $purchase->igst_amount, 2) }}</span>
                </div>
                <div class="d-flex justify-content-between py-2 border-top border-bottom fs-5 fw-bold text-main mt-2">
                    <span>Grand Total:</span>
                    <span class="text-warning-emphasis num-align">₹ {{ number_format($purchase->grand_total, 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- In Words -->
    <div class="p-3 border rounded-3 bg-light mb-4 small">
        <span class="text-muted">Bill Amount in Words:</span>
        <strong class="text-main d-block">{{ \App\Helpers\AccountingHelper::amountToWords($purchase->grand_total) }}</strong>
    </div>

    <div class="border-top pt-3 small text-muted text-center">
        Recorded into inventory ledger &bull; Voucher Ref #{{ $purchase->voucher->voucher_no ?? 'N/A' }}
    </div>
</div>
@endsection
