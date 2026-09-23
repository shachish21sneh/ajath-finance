@extends('layouts.app')

@section('title', 'Products & Services')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-1">Products & Services Master</h4>
        <p class="text-muted small mb-0">Track goods inventory, service items, HSN/SAC codes, and GST tax brackets</p>
    </div>
    <a href="{{ route('products.create') }}" class="btn btn-primary btn-sm fw-semibold">
        <i class="fa-solid fa-plus me-1"></i> New Item
    </a>
</div>

<div class="card card-modern p-4">
    <div class="table-responsive">
        <table id="productsTable" class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr class="small text-muted text-uppercase">
                    <th>Product / Service</th>
                    <th>Type</th>
                    <th>SKU / Barcode</th>
                    <th>HSN / SAC</th>
                    <th>GST Rate</th>
                    <th class="text-end">Selling Price</th>
                    <th class="text-end">Current Stock</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($products as $p)
                    <tr>
                        <td>
                            <div class="fw-bold text-main">{{ $p->name }}</div>
                            <div class="text-muted small">{{ $p->stockGroup->name ?? 'General Group' }}</div>
                        </td>
                        <td>
                            @if($p->item_type === 'goods')
                                <span class="badge bg-primary-subtle text-primary">Goods</span>
                            @else
                                <span class="badge bg-info-subtle text-info-emphasis">Service</span>
                            @endif
                        </td>
                        <td class="small">
                            <div>{{ $p->sku ?: '-' }}</div>
                            @if($p->barcode)<div class="text-muted" style="font-size: 0.72rem;"><i class="fa-solid fa-barcode me-1"></i>{{ $p->barcode }}</div>@endif
                        </td>
                        <td class="small">{{ $p->hsn_code ?: ($p->sac_code ?: '-') }}</td>
                        <td>
                            <span class="badge bg-light text-muted border">{{ $p->taxMaster->name ?? '0%' }}</span>
                        </td>
                        <td class="text-end fw-bold num-align">₹ {{ number_format($p->selling_price, 2) }}</td>
                        <td class="text-end fw-bold num-align">
                            @if($p->item_type === 'goods')
                                <span class="{{ $p->isLowStock() ? 'text-danger' : 'text-success' }}">
                                    {{ number_format($p->current_stock, 0) }} {{ $p->unit->symbol ?? 'PCS' }}
                                </span>
                                @if($p->isLowStock())
                                    <div class="text-danger small" style="font-size: 0.7rem;"><i class="fa-solid fa-triangle-exclamation"></i> Low</div>
                                @endif
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <a href="{{ route('products.edit', $p->id) }}" class="btn btn-sm btn-light border py-1 px-2" title="Edit product">
                                <i class="fa-solid fa-pen text-muted"></i>
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#productsTable').DataTable({ pageLength: 25 });
});
</script>
@endpush
