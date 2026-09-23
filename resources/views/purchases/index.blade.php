@extends('layouts.app')

@section('title', 'Purchase Invoices')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-3">
    <div>
        <h4 class="fw-bold mb-1">Purchase Invoices & Bills</h4>
        <p class="text-muted small mb-0">Record vendor procurement, Input Tax Credit (ITC), and inward inventory stock</p>
    </div>
    <a href="{{ route('purchases.create') }}" class="btn btn-primary btn-sm fw-semibold">
        <i class="fa-solid fa-plus me-1"></i> New Purchase Bill (F9)
    </a>
</div>

<div class="card card-modern p-4">
    <div class="table-responsive">
        <table id="purchasesTable" class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr class="small text-muted text-uppercase">
                    <th>Vendor Bill #</th>
                    <th>Date</th>
                    <th>Supplier Name</th>
                    <th>GSTIN</th>
                    <th class="text-end">Taxable</th>
                    <th class="text-end">Input GST</th>
                    <th class="text-end">Grand Total</th>
                    <th>Status</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoices as $inv)
                    <tr>
                        <td class="fw-bold">
                            <a href="{{ route('purchases.show', $inv->id) }}" class="text-decoration-none text-main">
                                {{ $inv->bill_no }}
                            </a>
                        </td>
                        <td class="small">{{ $inv->bill_date->format('d-M-Y') }}</td>
                        <td class="small fw-semibold">{{ $inv->supplier->name }}</td>
                        <td class="small text-muted">{{ $inv->supplier->gstin ?: 'Unregistered' }}</td>
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
                            <a href="{{ route('purchases.show', $inv->id) }}" class="btn btn-sm btn-light border py-1 px-2" title="View / Print Bill">
                                <i class="fa-solid fa-eye text-primary"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">No purchase bills recorded.</td>
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
