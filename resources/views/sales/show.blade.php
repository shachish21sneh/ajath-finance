@extends('layouts.app')

@section('title', 'Tax Invoice ' . $invoice->invoice_no)

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4 no-print">
    <div class="d-flex align-items-center gap-3">
        <a href="{{ route('sales.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fa-solid fa-arrow-left me-1"></i> Back to Invoices
        </a>
        <h4 class="fw-bold mb-0">Tax Invoice: {{ $invoice->invoice_no }}</h4>
    </div>
    <div class="d-flex gap-2">
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

    <!-- Bill To / Customer Details -->
    <div class="p-3 bg-light rounded-3 mb-4 small">
        <span class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Billed To / Buyer Details:</span>
        <div class="fw-bold fs-6 text-main mt-1">{{ $invoice->customer->name }}</div>
        <div>{{ $invoice->customer->address ?: 'Address on file' }}</div>
        <div><strong>GSTIN / UIN:</strong> {{ $invoice->customer->gstin ?: 'Unregistered Consumer (B2C)' }}</div>
        <div><strong>State & Code:</strong> {{ $invoice->customer->state }} ({{ $invoice->customer->state_code ?: $invoice->company->state_code }})</div>
    </div>

    <!-- Items Table -->
    <div class="table-responsive mb-4">
        <table class="table table-bordered align-middle small mb-0">
            <thead class="table-light text-uppercase" style="font-size: 0.75rem;">
                <tr>
                    <th style="width: 35px;" class="text-center">#</th>
                    <th>Item Description</th>
                    <th style="width: 80px;" class="text-center">HSN/SAC</th>
                    <th style="width: 70px;" class="text-end">Qty</th>
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
                            <div class="fw-bold text-main">{{ $item->description }}</div>
                        </td>
                        <td class="text-center text-muted">{{ $item->hsn_code ?: '-' }}</td>
                        <td class="text-end">{{ number_format($item->quantity, 0) }}</td>
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
    <div class="row g-4 mb-4">
        <!-- HSN Summary Table -->
        <div class="col-md-7 small">
            <h6 class="fw-bold mb-2 small text-uppercase text-muted">GST Tax Computation Breakdown</h6>
            <table class="table table-sm table-bordered text-center align-middle mb-0" style="font-size: 0.75rem;">
                <thead class="table-light">
                    <tr>
                        <th>Tax Component</th>
                        <th>Taxable Value</th>
                        <th>Rate</th>
                        <th>Tax Amount (₹)</th>
                    </tr>
                </thead>
                <tbody>
                    @if($invoice->cgst_amount > 0)
                        <tr>
                            <td>Central Tax (CGST)</td>
                            <td>₹ {{ number_format($invoice->taxable_amount, 2) }}</td>
                            <td>9.0%</td>
                            <td>₹ {{ number_format($invoice->cgst_amount, 2) }}</td>
                        </tr>
                    @endif
                    @if($invoice->sgst_amount > 0)
                        <tr>
                            <td>State Tax (SGST)</td>
                            <td>₹ {{ number_format($invoice->taxable_amount, 2) }}</td>
                            <td>9.0%</td>
                            <td>₹ {{ number_format($invoice->sgst_amount, 2) }}</td>
                        </tr>
                    @endif
                    @if($invoice->igst_amount > 0)
                        <tr>
                            <td>Integrated Tax (IGST)</td>
                            <td>₹ {{ number_format($invoice->taxable_amount, 2) }}</td>
                            <td>18.0%</td>
                            <td>₹ {{ number_format($invoice->igst_amount, 2) }}</td>
                        </tr>
                    @endif
                    <tr class="fw-bold table-light">
                        <td colspan="3" class="text-end">Total Output Tax:</td>
                        <td>₹ {{ number_format($invoice->cgst_amount + $invoice->sgst_amount + $invoice->igst_amount, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Grand Total Summary Box -->
        <div class="col-md-5">
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
    <div class="row g-4 mb-4 small">
        <div class="col-md-6">
            <h6 class="fw-bold text-uppercase mb-2 text-muted" style="font-size: 0.72rem;">Bank Account for Remittance (NEFT / RTGS)</h6>
            <div class="border p-3 rounded-3 bg-white">
                <div><strong>Bank Name:</strong> HDFC Bank Ltd</div>
                <div><strong>Account Name:</strong> {{ $invoice->company->name }}</div>
                <div><strong>Account Number:</strong> 50200012345678</div>
                <div><strong>IFSC Code:</strong> HDFC0000123</div>
                <div><strong>Branch:</strong> Connaught Place, New Delhi</div>
            </div>
        </div>
        <div class="col-md-6">
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
