@extends('layouts.app')

@section('title', 'Edit Company')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card card-modern p-4">
            <div class="d-flex align-items-center justify-content-between mb-4 border-bottom pb-3">
                <h5 class="fw-bold mb-0"><i class="fa-solid fa-pen-to-square me-2 text-primary"></i> Edit Company Profile</h5>
                <a href="{{ route('companies.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fa-solid fa-arrow-left me-1"></i> Back
                </a>
            </div>

            <form action="{{ route('companies.update', $company->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Company Trading Name *</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $company->name) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Legal / Registered Name</label>
                        <input type="text" name="legal_name" class="form-control" value="{{ old('legal_name', $company->legal_name) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">GSTIN (15 Digits)</label>
                        <input type="text" name="gstin" class="form-control" value="{{ old('gstin', $company->gstin) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">PAN Number (10 Digits)</label>
                        <input type="text" name="pan" class="form-control" value="{{ old('pan', $company->pan) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Official Email</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', $company->email) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Phone / Mobile</label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone', $company->phone) }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-semibold">Registered Address</label>
                        <textarea name="address" class="form-control" rows="2">{{ old('address', $company->address) }}</textarea>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">City</label>
                        <input type="text" name="city" class="form-control" value="{{ old('city', $company->city) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">State *</label>
                        <input type="text" name="state" class="form-control" value="{{ old('state', $company->state) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">State Code (GST) *</label>
                        <input type="text" name="state_code" class="form-control" value="{{ old('state_code', $company->state_code) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Currency Symbol *</label>
                        <input type="text" name="currency_symbol" class="form-control" value="{{ old('currency_symbol', $company->currency_symbol) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Currency Code *</label>
                        <input type="text" name="currency_code" class="form-control" value="{{ old('currency_code', $company->currency_code) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Pincode</label>
                        <input type="text" name="pincode" class="form-control" value="{{ old('pincode', $company->pincode) }}">
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                    <a href="{{ route('companies.index') }}" class="btn btn-light border px-4">Cancel</a>
                    <button type="submit" class="btn btn-primary px-4 fw-semibold">
                        <i class="fa-solid fa-check me-1"></i> Update Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
