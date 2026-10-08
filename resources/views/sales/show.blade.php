@extends('layouts.app')

@section('title', 'Tax Invoice ' . $invoice->invoice_no)

@push('styles')
<style>
@media print {
    @page {
        size: A4 portrait;
        margin: 8mm 8mm 8mm 8mm;
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
    .invoice-party-row {
        display: flex !important;
        flex-direction: row !important;
        flex-wrap: nowrap !important;
        gap: 12px !important;
        margin-bottom: 12px !important;
    }
    .invoice-party-col {
        flex: 0 0 50% !important;
        width: 50% !important;
        max-width: 50% !important;
        display: block !important;
    }
    .invoice-hsn-row, .invoice-bank-row {
        display: flex !important;
        flex-direction: row !important;
        flex-wrap: nowrap !important;
    }
    .bg-light {
        background-color: #f8fafc !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
}
</style>
@endpush

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4 no-print">
    <div class="d-flex align-items-center gap-3">
        <a href="{{ route('sales.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fa-solid fa-arrow-left me-1"></i> Back to Invoices
        </a>
        <h4 class="fw-bold mb-0">Tax Invoice: {{ $invoice->invoice_no }}</h4>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('sales.edit', $invoice->id) }}" class="btn btn-warning btn-sm fw-semibold">
            <i class="fa-solid fa-pen-to-square me-1"></i> Edit Invoice
        </a>
        <button type="button" class="btn btn-outline-primary btn-sm btn-trigger-print" onclick="window.print()">
            <i class="fa-solid fa-print me-1"></i> Print Tax Invoice (Ctrl+P)
        </button>
        <a href="{{ route('sales.create') }}" class="btn btn-primary btn-sm">
            <i class="fa-solid fa-plus me-1"></i> New Invoice (F8)
        </a>
    </div>
</div>

<div class="card card-modern p-4 p-md-5 print-invoice-sheet mx-auto shadow-sm" style="max-width: 950px; background: #ffffff; color: #1e293b;">
    <!-- Invoice Title Header -->
    <div class="text-center border-bottom pb-2 mb-3">
        <h5 class="fw-bold text-uppercase mb-0 tracking-wide">Tax Invoice</h5>
        <span class="small text-muted">(Issued under Section 31 of CGST Act, 2017)</span>
    </div>

    <!-- Company & Invoice Metadata Header -->
    <div class="row align-items-start border-bottom pb-3 mb-3">
        <div class="col-7">
            <h4 class="fw-bold text-main mb-1">{{ $invoice->company->name }}</h4>
            <div class="small text-muted">{{ $invoice->company->legal_name }}</div>
            <div class="small">{{ $invoice->company->address }}, {{ $invoice->company->city }}, {{ $invoice->company->state }} - {{ $invoice->company->pincode }}</div>
            <div class="small">
                <strong>GSTIN:</strong> {{ $invoice->company->gstin ?: 'N/A' }} | <strong>PAN:</strong> {{ $invoice->company->pan ?: 'N/A' }}
            </div>
            <div class="small">
                <strong>State:</strong> {{ $invoice->company->state }} (State Code: {{ $invoice->company->state_code }})
            </div>
        </div>
        <div class="col-5 text-end small">
            <table class="table table-sm table-borderless text-end mb-0">
                <tr>
                    <td class="text-muted py-0">Invoice No:</td>
                    <td class="fw-bold py-0">{{ $invoice->invoice_no }}</td>
                </tr>
                <tr>
                    <td class="text-muted py-0">Dated:</td>
                    <td class="fw-bold py-0">{{ $invoice->invoice_date->format('d-M-Y') }}</td>
                </tr>
                <tr>
                    <td class="text-muted py-0">Due Date:</td>
                    <td class="py-0">{{ $invoice->due_date ? $invoice->due_date->format('d-M-Y') : 'Due on Receipt' }}</td>
                </tr>
                <tr>
                    <td class="text-muted py-0">Place of Supply:</td>
                    <td class="py-0">{{ $invoice->customer->state ?? $invoice->company->state }}</td>
                </tr>
                <tr>
                    <td class="text-muted py-0">Reverse Charge:</td>
                    <td class="py-0">No</td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Bill To & Ship To Details -->
    <div class="row g-3 mb-3 invoice-party-row">
        <!-- Billed To / Buyer Details -->
        <div class="col-6 col-md-6 invoice-party-col">
            <div class="p-3 bg-light rounded-3 h-100 small border">
                <span class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Billed To / Buyer Details:</span>
                <div class="fw-bold fs-6 text-main mt-1">{{ $invoice->customer->name }}</div>
                <div>{{ $invoice->customer->address ?: 'Address on file' }}</div>
                @if($invoice->customer->city || $invoice->customer->pincode)
                    <div>{{ collect([$invoice->customer->city, $invoice->customer->pincode])->filter()->implode(' - ') }}</div>
                @endif
                <div><strong>GSTIN / UIN:</strong> {{ $invoice->customer->gstin ?: 'Unregistered Consumer (B2C)' }}</div>
                <div><strong>State & Code:</strong> {{ $invoice->customer->state }} ({{ $invoice->customer->state_code ?: $invoice->company->state_code }})</div>
                @if($invoice->customer->phone)
                    <div><strong>Phone:</strong> {{ $invoice->customer->phone }}</div>
                @endif
            </div>
        </div>

        <!-- Shipped To / Consignee Details -->
        <div class="col-6 col-md-6 invoice-party-col">
            <div class="p-3 bg-light rounded-3 h-100 small border">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Shipped To / Consignee Details:</span>
                    @if($invoice->hasCustomShippingAddress())
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size: 0.65rem;">Different Consignee</span>
                    @endif
                </div>
                @php
                    $shipName = $invoice->shipping_name ?: $invoice->customer->name;
                    $shipAddress = $invoice->shipping_address ?: ($invoice->customer->address ?: 'Same as billing address');
                    $shipCity = $invoice->shipping_city ?: $invoice->customer->city;
                    $shipPincode = $invoice->shipping_pincode ?: $invoice->customer->pincode;
                    $shipState = $invoice->shipping_state ?: $invoice->customer->state;
                    $shipStateCode = $invoice->shipping_state_code ?: ($invoice->customer->state_code ?: $invoice->company->state_code);
                    $shipGstin = $invoice->shipping_gstin ?: ($invoice->customer->gstin ?: 'Unregistered Consumer (B2C)');
                    $shipPhone = $invoice->shipping_phone ?: $invoice->customer->phone;
                @endphp
                <div class="fw-bold fs-6 text-main mt-1">{{ $shipName }}</div>
                <div>{{ $shipAddress }}</div>
                @if($shipCity || $shipPincode)
                    <div>{{ collect([$shipCity, $shipPincode])->filter()->implode(' - ') }}</div>
                @endif
                <div><strong>GSTIN / UIN:</strong> {{ $shipGstin }}</div>
                <div><strong>State & Code:</strong> {{ $shipState }} ({{ $shipStateCode }})</div>
                @if($shipPhone)
                    <div><strong>Phone:</strong> {{ $shipPhone }}</div>
                @endif
            </div>
        </div>
    </div>

    <!-- Items Table -->
    <div class="table-responsive mb-4">
        <table class="table table-bordered align-middle small mb-0">
            <thead class="table-light text-uppercase" style="font-size: 0.75rem;">
                <tr>
                    <th style="width: 35px;" class="text-center">#</th>
                    <th>Item Description</th>
                    <th style="width: 80px;" class="text-center">HSN/SAC</th>
                    <th style="width: 85px;" class="text-end">Qty</th>
                    <th style="width: 90px;" class="text-end">Rate (₹)</th>
                    <th style="width: 80px;" class="text-end">Disc (₹)</th>
                    <th style="width: 100px;" class="text-end">Taxable (₹)</th>
                    <th style="width: 70px;" class="text-center">GST %</th>
                    <th style="width: 110px;" class="text-end">Total (₹)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $idx => $item)
                    <tr>
                        <td class="text-center text-muted">{{ $idx + 1 }}</td>
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
                            @if($item->product && $item->product->has_inventory_components && $item->product->inventoryComponents->isNotEmpty())
                                <div class="mt-1" style="font-size: 0.72rem;">
                                    <span class="text-primary fw-semibold"><i class="fa-solid fa-boxes-stacked me-1"></i> Components Deducted:</span>
                                    <span class="text-muted">
                                        @foreach($item->product->inventoryComponents as $c)
                                            {{ (float)$c->quantity * (float)$item->quantity }}x {{ $c->name }}{{ !$loop->last ? ', ' : '' }}
                                        @endforeach
                                    </span>
                                </div>
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
                        <td class="text-end num-align text-muted">{{ (float)$item->discount_amount > 0 ? '₹ ' . number_format($item->discount_amount, 2) : '-' }}</td>
                        <td class="text-end num-align fw-semibold">₹ {{ number_format($item->taxable_amount, 2) }}</td>
                        <td class="text-center">{{ (float)$item->gst_rate }}%</td>
                        <td class="text-end num-align fw-bold">₹ {{ number_format($item->total_amount, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot class="table-light">
                <tr class="fw-bold">
                    <td colspan="6" class="text-end text-uppercase">Total Taxable Value:</td>
                    <td class="text-end num-align">₹ {{ number_format($invoice->taxable_amount, 2) }}</td>
                    <td></td>
                    <td class="text-end num-align fs-6">₹ {{ number_format($invoice->subtotal + $invoice->cgst_amount + $invoice->sgst_amount + $invoice->igst_amount, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Taxes Calculation & Grand Total Breakdown -->
    <div class="row g-3 mb-3 invoice-hsn-row">
        <!-- HSN Summary Table -->
        <div class="col-7 col-md-7 small">
            <h6 class="fw-bold mb-2 small text-uppercase text-muted">GST Tax Computation Breakdown</h6>
            @php
                $hsnBreakdown = [];
                $isIgst = (float)$invoice->igst_amount > 0 || ((float)$invoice->cgst_amount == 0 && (float)$invoice->sgst_amount == 0);

                foreach ($invoice->items as $item) {
                    $key = ($item->hsn_code ?: 'N/A') . '_' . (float)$item->gst_rate;
                    if (!isset($hsnBreakdown[$key])) {
                        $hsnBreakdown[$key] = [
                            'hsn' => $item->hsn_code ?: ($item->product?->name ?: ($item->description ?: 'N/A')),
                            'rate' => (float)$item->gst_rate,
                            'taxable' => 0.0,
                            'cgst' => 0.0,
                            'sgst' => 0.0,
                            'igst' => 0.0,
                            'total_tax' => 0.0,
                        ];
                    }
                    $hsnBreakdown[$key]['taxable'] += (float)$item->taxable_amount;
                    $hsnBreakdown[$key]['cgst'] += (float)$item->cgst_amount;
                    $hsnBreakdown[$key]['sgst'] += (float)$item->sgst_amount;
                    $hsnBreakdown[$key]['igst'] += (float)$item->igst_amount;
                    $hsnBreakdown[$key]['total_tax'] += ((float)$item->cgst_amount + (float)$item->sgst_amount + (float)$item->igst_amount);
                }

                $totalTaxable = array_sum(array_column($hsnBreakdown, 'taxable'));
                $totalCgst = array_sum(array_column($hsnBreakdown, 'cgst'));
                $totalSgst = array_sum(array_column($hsnBreakdown, 'sgst'));
                $totalIgst = array_sum(array_column($hsnBreakdown, 'igst'));
                $totalTax = array_sum(array_column($hsnBreakdown, 'total_tax'));
            @endphp
            <div class="table-responsive">
                <table class="table table-sm table-bordered align-middle mb-0 text-center" style="font-size: 0.78rem; border-color: #cbd5e1;">
                    <thead class="table-light text-dark">
                        @if($isIgst)
                            <tr class="align-middle">
                                <th rowspan="2" class="text-center align-middle" style="min-width: 110px;">HSN/SAC</th>
                                <th rowspan="2" class="text-end align-middle" style="min-width: 95px;">Taxable<br>Value</th>
                                <th colspan="2" class="text-center">IGST</th>
                                <th rowspan="2" class="text-end align-middle" style="min-width: 95px;">Total<br>Tax Amount</th>
                            </tr>
                            <tr class="align-middle">
                                <th class="text-center" style="width: 55px;">Rate</th>
                                <th class="text-end" style="width: 85px;">Amount</th>
                            </tr>
                        @else
                            <tr class="align-middle">
                                <th rowspan="2" class="text-center align-middle" style="min-width: 90px;">HSN/SAC</th>
                                <th rowspan="2" class="text-end align-middle" style="min-width: 85px;">Taxable<br>Value</th>
                                <th colspan="2" class="text-center">Central Tax</th>
                                <th colspan="2" class="text-center">State Tax</th>
                                <th rowspan="2" class="text-end align-middle" style="min-width: 85px;">Total<br>Tax Amount</th>
                            </tr>
                            <tr class="align-middle">
                                <th class="text-center" style="width: 50px;">Rate</th>
                                <th class="text-end" style="width: 70px;">Amount</th>
                                <th class="text-center" style="width: 50px;">Rate</th>
                                <th class="text-end" style="width: 70px;">Amount</th>
                            </tr>
                        @endif
                    </thead>
                    <tbody>
                        @foreach($hsnBreakdown as $row)
                            <tr>
                                <td class="text-start ps-2 fw-semibold">{{ $row['hsn'] }}</td>
                                <td class="text-end num-align">{{ number_format($row['taxable'], 2) }}</td>
                                @if($isIgst)
                                    <td class="text-center">{{ (float)$row['rate'] }}%</td>
                                    <td class="text-end num-align">{{ number_format($row['igst'], 2) }}</td>
                                @else
                                    <td class="text-center">{{ (float)($row['rate'] / 2) }}%</td>
                                    <td class="text-end num-align">{{ number_format($row['cgst'], 2) }}</td>
                                    <td class="text-center">{{ (float)($row['rate'] / 2) }}%</td>
                                    <td class="text-end num-align">{{ number_format($row['sgst'], 2) }}</td>
                                @endif
                                <td class="text-end num-align fw-semibold">{{ number_format($row['total_tax'], 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td class="text-end">Total</td>
                            <td class="text-end num-align">{{ number_format($totalTaxable, 2) }}</td>
                            @if($isIgst)
                                <td></td>
                                <td class="text-end num-align">{{ number_format($totalIgst, 2) }}</td>
                            @else
                                <td></td>
                                <td class="text-end num-align">{{ number_format($totalCgst, 2) }}</td>
                                <td></td>
                                <td class="text-end num-align">{{ number_format($totalSgst, 2) }}</td>
                            @endif
                            <td class="text-end num-align">{{ number_format($totalTax, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Grand Total Summary Box -->
        <div class="col-5 col-md-5">
            <div class="p-3 bg-light rounded-3 small">
                <div class="d-flex justify-content-between py-1">
                    <span class="text-muted">Taxable Subtotal:</span>
                    <span class="fw-semibold num-align">₹ {{ number_format($invoice->taxable_amount, 2) }}</span>
                </div>
                <div class="d-flex justify-content-between py-1">
                    <span class="text-muted">Total Output GST:</span>
                    <span class="fw-semibold num-align">₹ {{ number_format($invoice->cgst_amount + $invoice->sgst_amount + $invoice->igst_amount, 2) }}</span>
                </div>
                @if(abs($invoice->round_off) > 0)
                    <div class="d-flex justify-content-between py-1 text-muted">
                        <span>Round Off:</span>
                        <span class="num-align">₹ {{ number_format($invoice->round_off, 2) }}</span>
                    </div>
                @endif
                <div class="d-flex justify-content-between py-2 border-top border-bottom fs-5 fw-bold text-main mt-2">
                    <span>Invoice Total:</span>
                    <span class="text-primary num-align">₹ {{ number_format($invoice->grand_total, 2) }}</span>
                </div>
                <div class="d-flex justify-content-between py-1 mt-1 text-muted">
                    <span>Payment Status:</span>
                    <span class="fw-bold text-uppercase text-success">{{ $invoice->payment_status }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Amount in Words -->
    <div class="p-3 border rounded-3 bg-light mb-4 small">
        <span class="text-muted">Invoice Value (in Words):</span>
        <strong class="text-main d-block fs-6">{{ \App\Helpers\AccountingHelper::amountToWords($invoice->grand_total) }}</strong>
    </div>

    <!-- Bank Details & Terms -->
    <div class="row g-3 mb-3 small invoice-bank-row">
        <div class="col-6 col-md-6">
            <h6 class="fw-bold text-uppercase mb-2 text-muted" style="font-size: 0.72rem;">Bank Account for Remittance (NEFT / RTGS)</h6>
            <div class="border p-3 rounded-3 bg-white">
                <div><strong>Bank Name:</strong> HDFC Bank Ltd</div>
                <div><strong>Account Name:</strong> {{ $invoice->company->name }}</div>
                <div><strong>Account Number:</strong> 50200012345678</div>
                <div><strong>IFSC Code:</strong> HDFC0000123</div>
                <div><strong>Branch:</strong> Connaught Place, New Delhi</div>
            </div>
        </div>
        <div class="col-6 col-md-6">
            <h6 class="fw-bold text-uppercase mb-2 text-muted" style="font-size: 0.72rem;">Terms & Conditions</h6>
            <div class="text-muted small">
                {!! nl2br(e($invoice->terms_conditions)) !!}
            </div>
        </div>
    </div>

    <!-- Authorized Signatory -->
    <div class="row pt-4 mt-3 text-center align-items-end">
        <div class="col-6 text-start small text-muted">
            <div>Electronic Reference ID: #INV-{{ $invoice->id }}-{{ date('Ymd') }}</div>
            <div>This is a computer generated legal tax invoice.</div>
        </div>
        <div class="col-6 text-end">
            <div class="small fw-bold mb-5">For {{ $invoice->company->name }}</div>
            <div class="border-top d-inline-block pt-1 px-4 small text-muted">Authorized Signatory</div>
        </div>
    </div>
</div>
@endsection
