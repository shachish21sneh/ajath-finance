@extends('layouts.app')

@section('title', 'GST Summary & Reports')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-3 no-print">
    <div>
        <h4 class="fw-bold mb-1"><i class="fa-solid fa-file-shield text-success me-2"></i> GST Return Summary (GSTR-1)</h4>
        <p class="text-muted small mb-0">Outward supplies summary for GST compliance, B2B, B2C, and HSN reporting</p>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-outline-primary btn-sm btn-trigger-print" onclick="window.print()">
            <i class="fa-solid fa-print me-1"></i> Print GSTR-1 Summary (Ctrl+P)
        </button>
    </div>
</div>

<!-- Date Filter -->
<div class="card card-modern p-3 mb-4 no-print">
    <form method="GET" action="{{ route('reports.gst') }}" class="row g-2 align-items-end">
        <div class="col-md-3">
            <label class="form-label small fw-semibold">From Date</label>
            <input type="date" name="from_date" class="form-control form-control-sm" value="{{ $fromDate }}">
        </div>
        <div class="col-md-3">
            <label class="form-label small fw-semibold">To Date</label>
            <input type="date" name="to_date" class="form-control form-control-sm" value="{{ $toDate }}">
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary btn-sm w-100 fw-semibold">Update Summary</button>
        </div>
    </form>
</div>

<!-- KPI Cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card card-modern stat-card border-start border-primary border-4">
            <div class="stat-label">Total Taxable Value</div>
            <div class="stat-value text-primary">₹ {{ number_format($gstr1['total_taxable'], 2) }}</div>
            <span class="text-muted small">{{ $gstr1['total_invoices'] }} Invoices Filed</span>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card card-modern stat-card border-start border-success border-4">
            <div class="stat-label">Central Tax (CGST)</div>
            <div class="stat-value text-success">₹ {{ number_format($gstr1['total_cgst'], 2) }}</div>
            <span class="text-muted small">Intra-state Tax</span>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card card-modern stat-card border-start border-info border-4">
            <div class="stat-label">State Tax (SGST)</div>
            <div class="stat-value text-info-emphasis">₹ {{ number_format($gstr1['total_sgst'], 2) }}</div>
            <span class="text-muted small">Intra-state Tax</span>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card card-modern stat-card border-start border-warning border-4">
            <div class="stat-label">Integrated Tax (IGST)</div>
            <div class="stat-value text-warning-emphasis">₹ {{ number_format($gstr1['total_igst'], 2) }}</div>
            <span class="text-muted small">Inter-state Supplies</span>
        </div>
    </div>
</div>

<!-- B2B Invoices Section -->
<div class="card card-modern p-4 mb-4">
    <h6 class="fw-bold mb-3"><i class="fa-solid fa-briefcase text-primary me-2"></i> 4A, 4B, 4C - B2B Invoices (Registered Buyers)</h6>
    <div class="table-responsive">
        <table class="table table-bordered align-middle small mb-0">
            <thead class="table-light text-uppercase">
                <tr>
                    <th>Invoice #</th>
                    <th>Date</th>
                    <th>Customer Name</th>
                    <th>Buyer GSTIN</th>
                    <th>Place of Supply</th>
                    <th class="text-end">Taxable Value</th>
                    <th class="text-end">CGST</th>
                    <th class="text-end">SGST</th>
                    <th class="text-end">IGST</th>
                    <th class="text-end">Invoice Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse($gstr1['b2b_invoices'] as $b)
                    <tr>
                        <td class="fw-bold">{{ $b['invoice_no'] }}</td>
                        <td>{{ $b['invoice_date'] }}</td>
                        <td class="fw-semibold">{{ $b['customer_name'] }}</td>
                        <td>{{ $b['customer_gstin'] }}</td>
                        <td>{{ $b['place_of_supply'] }}</td>
                        <td class="text-end num-align">₹ {{ number_format($b['taxable_amount'], 2) }}</td>
                        <td class="text-end num-align">₹ {{ number_format($b['cgst_amount'], 2) }}</td>
                        <td class="text-end num-align">₹ {{ number_format($b['sgst_amount'], 2) }}</td>
                        <td class="text-end num-align">₹ {{ number_format($b['igst_amount'], 2) }}</td>
                        <td class="text-end fw-bold num-align">₹ {{ number_format($b['grand_total'], 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center text-muted py-3">No B2B registered invoices in this period.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- HSN Summary Section -->
<div class="card card-modern p-4 mb-4">
    <h6 class="fw-bold mb-3"><i class="fa-solid fa-layer-group text-primary me-2"></i> 12 - HSN/SAC Summary of Outward Supplies</h6>
    <div class="table-responsive">
        <table class="table table-bordered align-middle small mb-0">
            <thead class="table-light text-uppercase">
                <tr>
                    <th>HSN / SAC</th>
                    <th>Description</th>
                    <th class="text-end">Total Qty</th>
                    <th class="text-end">Total Taxable Value</th>
                    <th class="text-end">Central Tax</th>
                    <th class="text-end">State Tax</th>
                    <th class="text-end">Integrated Tax</th>
                    <th class="text-end">Total Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse($gstr1['hsn_summary'] as $h)
                    <tr>
                        <td class="fw-bold">{{ $h['hsn'] }}</td>
                        <td>{{ $h['description'] }}</td>
                        <td class="text-end">{{ number_format($h['total_quantity'], 0) }}</td>
                        <td class="text-end num-align">₹ {{ number_format($h['taxable_value'], 2) }}</td>
                        <td class="text-end num-align">₹ {{ number_format($h['cgst'], 2) }}</td>
                        <td class="text-end num-align">₹ {{ number_format($h['sgst'], 2) }}</td>
                        <td class="text-end num-align">₹ {{ number_format($h['igst'], 2) }}</td>
                        <td class="text-end fw-bold num-align">₹ {{ number_format($h['total'], 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-3">No HSN entries recorded.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
