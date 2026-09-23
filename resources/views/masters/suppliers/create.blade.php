@extends('layouts.app')

@section('title', 'Add Supplier')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card card-modern p-4">
            <div class="d-flex align-items-center justify-content-between mb-4 border-bottom pb-3">
                <h5 class="fw-bold mb-0"><i class="fa-solid fa-truck-field me-2 text-primary"></i> Register New Supplier / Vendor</h5>
                <a href="{{ route('suppliers.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fa-solid fa-arrow-left me-1"></i> Back
                </a>
            </div>

            <form action="{{ route('suppliers.store') }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label small fw-semibold">Supplier / Vendor Name *</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" required placeholder="e.g. Sunrise Components Ltd">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Supplier Code</label>
                        <input type="text" name="code" class="form-control" value="{{ old('code') }}" placeholder="e.g. SUPP-005">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">GSTIN</label>
                        <input type="text" name="gstin" class="form-control" value="{{ old('gstin') }}" placeholder="15-digit GSTIN">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">PAN Number</label>
                        <input type="text" name="pan" class="form-control" value="{{ old('pan') }}" placeholder="10-digit PAN">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Email Address</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="sales@vendor.com">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Phone / Mobile</label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone') }}" placeholder="+91...">
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-semibold">Vendor Address</label>
                        <textarea name="address" class="form-control" rows="2" placeholder="Street, Building, Area...">{{ old('address') }}</textarea>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">City</label>
                        <input type="text" name="city" class="form-control" value="{{ old('city') }}" placeholder="City">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">State *</label>
                        <input type="text" name="state" class="form-control" value="{{ old('state', $company->state) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">State Code *</label>
                        <input type="text" name="state_code" class="form-control" value="{{ old('state_code', $company->state_code) }}" required placeholder="07">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Pincode</label>
                        <input type="text" name="pincode" class="form-control" value="{{ old('pincode') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Credit Limit (₹)</label>
                        <input type="number" step="0.01" name="credit_limit" class="form-control" value="{{ old('credit_limit', '0.00') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Credit Days</label>
                        <input type="number" name="credit_days" class="form-control" value="{{ old('credit_days', 30) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Opening Balance (₹)</label>
                        <input type="number" step="0.01" name="opening_balance" class="form-control" value="{{ old('opening_balance', '0.00') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Balance Type</label>
                        <select name="opening_balance_type" class="form-select">
                            <option value="Cr">Cr (Payable to supplier)</option>
                            <option value="Dr">Dr (Advance paid to supplier)</option>
                        </select>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                    <a href="{{ route('suppliers.index') }}" class="btn btn-light border px-4">Cancel</a>
                    <button type="submit" class="btn btn-primary px-4 fw-semibold">Save Supplier</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
