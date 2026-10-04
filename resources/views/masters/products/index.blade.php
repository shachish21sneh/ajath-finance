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
                            @if($p->has_inventory_components && $p->inventoryComponents->count() > 0)
                                <div class="mt-1">
                                    <button type="button" class="btn btn-xs py-0 px-2 btn-outline-primary border-primary-subtle text-primary rounded-pill small"
                                            style="font-size: 0.72rem;"
                                            data-bs-toggle="modal" data-bs-target="#componentsModal_{{ $p->id }}">
                                        <i class="fa-solid fa-boxes-stacked me-1"></i> {{ $p->inventoryComponents->count() }} Linked Items
                                    </button>
                                </div>

                                <!-- Components View Modal -->
                                <div class="modal fade text-start" id="componentsModal_{{ $p->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content shadow border-0">
                                            <div class="modal-header py-2 bg-light">
                                                <h6 class="modal-title fw-bold text-main mb-0">
                                                    <i class="fa-solid fa-boxes-stacked text-primary me-2"></i> Inventory Components: {{ $p->name }}
                                                </h6>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body p-3">
                                                <p class="small text-muted mb-2">When this item is sold, stock for the following components is automatically deducted:</p>
                                                <div class="table-responsive">
                                                    <table class="table table-sm table-bordered align-middle mb-0">
                                                        <thead class="table-light small text-muted">
                                                            <tr>
                                                                <th>Component Item</th>
                                                                <th class="text-center">HSN</th>
                                                                <th class="text-end">Qty Used</th>
                                                                <th class="text-center">Live Stock</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="small">
                                                            @foreach($p->inventoryComponents as $comp)
                                                                <tr>
                                                                    <td class="fw-semibold">{{ $comp->name }}</td>
                                                                    <td class="text-center text-muted">{{ $comp->hsn_code ?: '-' }}</td>
                                                                    <td class="text-end font-monospace">{{ $comp->quantity }}</td>
                                                                    <td class="text-center">
                                                                        @if($comp->componentProduct)
                                                                            <span class="badge {{ $comp->componentProduct->current_stock > 0 ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }} border">
                                                                                {{ $comp->componentProduct->current_stock }} {{ $comp->componentProduct->unit->symbol ?? '' }}
                                                                            </span>
                                                                        @else
                                                                            <span class="text-muted">-</span>
                                                                        @endif
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                            <div class="modal-footer py-2 bg-light">
                                                <a href="{{ route('products.edit', $p->id) }}" class="btn btn-sm btn-primary">
                                                    <i class="fa-solid fa-pen-to-square me-1"></i> Edit Components
                                                </a>
                                                <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Close</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
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
