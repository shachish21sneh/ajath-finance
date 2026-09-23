@extends('layouts.app')

@section('title', 'Sales Invoices')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-3">
    <div>
        <h4 class="fw-bold mb-1">Sales Invoices & Billing</h4>
        <p class="text-muted small mb-0">GST-compliant tax invoices, POS counter bills, and delivery challans</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('sales.pos') }}" class="btn btn-warning btn-sm fw-bold">
            <i class="fa-solid fa-cash-register me-1"></i> POS Counter
        </a>
        <a href="{{ route('sales.create') }}" class="btn btn-primary btn-sm fw-semibold">
            <i class="fa-solid fa-plus me-1"></i> New Invoice (F8)
        </a>
    </div>
</div>

<div class="card card-modern p-4">
    <div class="table-responsive">
        <table id="salesTable" class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr class="small text-muted text-uppercase">
                    <th>Invoice #</th>
                    <th>Date</th>
                    <th>Customer Name</th>
                    <th>GSTIN</th>
                    <th class="text-end">Taxable</th>
                    <th class="text-end">Tax (GST)</th>
                    <th class="text-end">Grand Total</th>
                    <th>Payment</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoices as $inv)
                    <tr>
                        <td class="fw-bold">
                            <a href="{{ route('sales.show', $inv->id) }}" class="text-decoration-none text-main">
                                {{ $inv->invoice_no }}
                            </a>
                            @if($inv->isPos())
                                <span class="badge bg-warning-subtle text-warning-emphasis ms-1" style="font-size: 0.65rem;">POS</span>
                            @endif
                        </td>
                        <td class="small">{{ $inv->invoice_date->format('d-M-Y') }}</td>
                        <td class="small fw-semibold">{{ $inv->customer->name ?? 'Walk-in Customer' }}</td>
                        <td class="small text-muted">{{ $inv->customer->gstin ?: 'Unregistered' }}</td>
                        <td class="text-end small num-align">₹ {{ number_format($inv->taxable_amount, 2) }}</td>
                        <td class="text-end small num-align text-muted">₹ {{ number_format($inv->cgst_amount + $inv->sgst_amount + $inv->igst_amount, 2) }}</td>
                        <td class="text-end fw-bold num-align">₹ {{ number_format($inv->grand_total, 2) }}</td>
                        <td>
                            @php
                                $statusBadge = match($inv->payment_status) {
                                    'paid' => 'bg-success-subtle text-success',
                                    'partial' => 'bg-warning-subtle text-warning-emphasis',
                                    default => 'bg-danger-subtle text-danger',
                                };
                            @endphp
                            <span class="badge {{ $statusBadge }} text-capitalize">{{ $inv->payment_status }}</span>
                        </td>
                        <td class="text-center">
                            <a href="{{ route('sales.show', $inv->id) }}" class="btn btn-sm btn-light border py-1 px-2" title="View / Print Invoice">
                                <i class="fa-solid fa-file-invoice text-primary"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">No sales invoices recorded yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $invoices->links() }}
    </div>
</div>
@endsection
