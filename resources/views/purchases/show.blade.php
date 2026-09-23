@extends('layouts.app')

@section('title', 'Purchase Bill ' . $purchase->bill_no)

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4 no-print">
    <div class="d-flex align-items-center gap-3">
        <a href="{{ route('purchases.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fa-solid fa-arrow-left me-1"></i> Back to Bills
        </a>
        <h4 class="fw-bold mb-0">Vendor Bill: {{ $purchase->bill_no }}</h4>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-outline-primary btn-sm btn-trigger-print" onclick="window.print()">
            <i class="fa-solid fa-print me-1"></i> Print Bill (Ctrl+P)
        </button>
        <a href="{{ route('purchases.create') }}" class="btn btn-primary btn-sm">
            <i class="fa-solid fa-plus me-1"></i> New Purchase (F9)
        </a>
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
                    <th style="width: 70px;" class="text-end">Qty</th>
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
                            <div class="fw-bold text-main">{{ $item->description }}</div>
                        </td>
                        <td class="text-center text-muted">{{ $item->hsn_code ?: '-' }}</td>
                        <td class="text-end">{{ number_format($item->quantity, 0) }}</td>
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
