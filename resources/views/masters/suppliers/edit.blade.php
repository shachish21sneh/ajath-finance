@extends('layouts.app')

@section('title', 'Edit Supplier')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card card-modern p-4">
            <div class="d-flex align-items-center justify-content-between mb-4 border-bottom pb-3">
                <h5 class="fw-bold mb-0"><i class="fa-solid fa-pen-to-square me-2 text-primary"></i> Edit Supplier Account</h5>
                <a href="{{ route('suppliers.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fa-solid fa-arrow-left me-1"></i> Back
                </a>
            </div>

            <form action="{{ route('suppliers.update', $supplier->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label small fw-semibold">Supplier / Vendor Name *</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $supplier->name) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Supplier Code</label>
                        <input type="text" name="code" class="form-control" value="{{ old('code', $supplier->code) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">GSTIN</label>
                        <input type="text" name="gstin" class="form-control" value="{{ old('gstin', $supplier->gstin) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">PAN Number</label>
                        <input type="text" name="pan" class="form-control" value="{{ old('pan', $supplier->pan) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Email Address</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', $supplier->email) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Phone / Mobile</label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone', $supplier->phone) }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-semibold">Vendor Address</label>
                        <textarea name="address" class="form-control" rows="2">{{ old('address', $supplier->address) }}</textarea>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">City</label>
                        <input type="text" name="city" class="form-control" value="{{ old('city', $supplier->city) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">State *</label>
                        <input type="text" name="state" class="form-control" value="{{ old('state', $supplier->state) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">State Code *</label>
                        <input type="text" name="state_code" class="form-control" value="{{ old('state_code', $supplier->state_code) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Pincode</label>
                        <input type="text" name="pincode" class="form-control" value="{{ old('pincode', $supplier->pincode) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Credit Limit (₹)</label>
                        <input type="number" step="0.01" name="credit_limit" class="form-control" value="{{ old('credit_limit', $supplier->credit_limit) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Credit Days</label>
                        <input type="number" name="credit_days" class="form-control" value="{{ old('credit_days', $supplier->credit_days) }}">
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                    <a href="{{ route('suppliers.index') }}" class="btn btn-light border px-4">Cancel</a>
                    <button type="submit" class="btn btn-primary px-4 fw-semibold">Update Supplier</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
