@extends('layouts.app')

@section('title', 'Edit Customer')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card card-modern p-4">
            <div class="d-flex align-items-center justify-content-between mb-4 border-bottom pb-3">
                <h5 class="fw-bold mb-0"><i class="fa-solid fa-pen-to-square me-2 text-primary"></i> Edit Customer Account</h5>
                <a href="{{ route('customers.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fa-solid fa-arrow-left me-1"></i> Back
                </a>
            </div>

            <form action="{{ route('customers.update', $customer->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label small fw-semibold">Customer / Company Name *</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $customer->name) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Customer Code</label>
                        <input type="text" name="code" class="form-control" value="{{ old('code', $customer->code) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">GSTIN</label>
                        <input type="text" name="gstin" class="form-control" value="{{ old('gstin', $customer->gstin) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">PAN Number</label>
                        <input type="text" name="pan" class="form-control" value="{{ old('pan', $customer->pan) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Email Address</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', $customer->email) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Phone / Mobile</label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone', $customer->phone) }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-semibold">Billing Address</label>
                        <textarea name="address" class="form-control" rows="2">{{ old('address', $customer->address) }}</textarea>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">City</label>
                        <input type="text" name="city" class="form-control" value="{{ old('city', $customer->city) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">State *</label>
                        <input type="text" name="state" class="form-control" value="{{ old('state', $customer->state) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">State Code *</label>
                        <input type="text" name="state_code" class="form-control" value="{{ old('state_code', $customer->state_code) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Pincode</label>
                        <input type="text" name="pincode" class="form-control" value="{{ old('pincode', $customer->pincode) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Credit Limit (₹)</label>
                        <input type="number" step="0.01" name="credit_limit" class="form-control" value="{{ old('credit_limit', $customer->credit_limit) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Credit Days Allowed</label>
                        <input type="number" name="credit_days" class="form-control" value="{{ old('credit_days', $customer->credit_days) }}">
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                    <a href="{{ route('customers.index') }}" class="btn btn-light border px-4">Cancel</a>
                    <button type="submit" class="btn btn-primary px-4 fw-semibold">Update Customer</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
