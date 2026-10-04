@extends('layouts.app')

@section('title', 'Edit Product')

@section('content')
<div class="card card-modern p-4 p-md-5">
    <div class="d-flex align-items-center justify-content-between mb-4 border-bottom pb-3">
        <h5 class="fw-bold mb-0"><i class="fa-solid fa-pen-to-square me-2 text-primary"></i> Edit Item / Service</h5>
        <a href="{{ route('products.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fa-solid fa-arrow-left me-1"></i> Back
        </a>
    </div>

    <form action="{{ route('products.update', $product->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="row g-4">
            <div class="col-md-8">
                <label class="form-label small fw-semibold">Item Name *</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $product->name) }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Item Classification *</label>
                <select name="item_type" class="form-select" required>
                    <option value="goods" {{ $product->item_type === 'goods' ? 'selected' : '' }}>Physical Goods (Inventory)</option>
                    <option value="service" {{ $product->item_type === 'service' ? 'selected' : '' }}>Service (Non-inventory)</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold">SKU Code</label>
                <input type="text" name="sku" class="form-control" value="{{ old('sku', $product->sku) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Barcode / EAN</label>
                <input type="text" name="barcode" class="form-control" value="{{ old('barcode', $product->barcode) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold">HSN / SAC Code</label>
                <input type="text" name="hsn_code" class="form-control" value="{{ old('hsn_code', $product->hsn_code) }}">
            </div>
            <div class="col-md-4">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <label class="form-label small fw-semibold mb-0">Stock Group</label>
                    <button type="button" class="btn btn-link p-0 text-primary text-decoration-none" data-bs-toggle="modal" data-bs-target="#quickAddStockGroupModal" title="Add New Stock Group">
                        <i class="fa-solid fa-circle-plus fs-5"></i>
                    </button>
                </div>
                <select name="stock_group_id" id="product_stock_group_id" class="form-select">
                    <option value="" data-hsn="" data-tax-id="">-- No Group --</option>
                    @foreach($groups as $g)
                        <option value="{{ $g->id }}" data-hsn="{{ $g->hsn_code }}" data-tax-id="{{ $g->tax_master_id }}" {{ $product->stock_group_id == $g->id ? 'selected' : '' }}>{{ $g->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <label class="form-label small fw-semibold mb-0">Base Measurement Unit</label>
                    <button type="button" class="btn btn-link p-0 text-success text-decoration-none" data-bs-toggle="modal" data-bs-target="#quickAddUnitModal" title="Add New Measurement Unit">
                        <i class="fa-solid fa-circle-plus fs-5"></i>
                    </button>
                </div>
                <select name="unit_id" id="product_unit_id" class="form-select">
                    @foreach($units as $u)
                        <option value="{{ $u->id }}" {{ $product->unit_id == $u->id ? 'selected' : '' }}>{{ $u->name }} ({{ $u->symbol }})</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <label class="form-label small fw-semibold mb-0">Applicable GST Rate</label>
                    <button type="button" class="btn btn-link p-0 text-primary text-decoration-none" data-bs-toggle="modal" data-bs-target="#quickAddTaxModal" title="Add New GST Rate">
                        <i class="fa-solid fa-circle-plus fs-5"></i>
                    </button>
                </div>
                <select name="tax_master_id" id="product_tax_master_id" class="form-select">
                    <option value="">-- Exempt / Zero --</option>
                    @foreach($taxes as $t)
                        <option value="{{ $t->id }}" {{ $product->tax_master_id == $t->id ? 'selected' : '' }}>{{ $t->name }} ({{ $t->rate }}%)</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label small fw-semibold">Purchase Price (₹) *</label>
                <input type="number" step="0.01" name="purchase_price" class="form-control" value="{{ old('purchase_price', $product->purchase_price) }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Selling Price (₹) *</label>
                <input type="number" step="0.01" name="selling_price" class="form-control" value="{{ old('selling_price', $product->selling_price) }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Maximum Retail Price (MRP)</label>
                <input type="number" step="0.01" name="mrp" class="form-control" value="{{ old('mrp', $product->mrp) }}">
            </div>

            <div class="col-md-6">
                <label class="form-label small fw-semibold">Current Live Stock</label>
                <input type="text" class="form-control" value="{{ $product->current_stock }} {{ $product->unit->symbol ?? '' }}" disabled readonly>
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-semibold">Reorder Warning Level</label>
                <input type="number" step="0.01" name="reorder_level" class="form-control" value="{{ old('reorder_level', $product->reorder_level) }}">
            </div>
            <div class="col-12">
                <label class="form-label small fw-semibold">Description / Specifications</label>
                <textarea name="description" class="form-control" rows="2">{{ old('description', $product->description) }}</textarea>
            </div>
        </div>

        @include('masters.products.partials.inventory_use_section')

        <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
            <a href="{{ route('products.index') }}" class="btn btn-light border px-4">Cancel</a>
            <button type="submit" class="btn btn-primary px-4 fw-semibold">Update Item</button>
        </div>
    </form>
</div>

@include('masters.products.partials.quick_create_modals')
@endsection
