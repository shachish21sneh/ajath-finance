@extends('layouts.app')

@section('title', 'Create Ledger')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card card-modern p-4">
            <div class="d-flex align-items-center justify-content-between mb-4 border-bottom pb-3">
                <h5 class="fw-bold mb-0"><i class="fa-solid fa-plus-circle me-2 text-primary"></i> Create General Ledger Account</h5>
                <a href="{{ route('ledgers.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fa-solid fa-arrow-left me-1"></i> Back
                </a>
            </div>

            <form action="{{ route('ledgers.store') }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label small fw-semibold">Ledger Name *</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" required placeholder="e.g. Office Rent, Freight Charges, ABC Traders">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Ledger Code / Alias</label>
                        <input type="text" name="code" class="form-control" value="{{ old('code') }}" placeholder="e.g. EXP-042">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Parent Master Group *</label>
                        <select name="ledger_group_id" class="form-select" required>
                            @foreach($groups as $g)
                                <option value="{{ $g->id }}" {{ old('ledger_group_id') == $g->id ? 'selected' : '' }}>
                                    [{{ $g->nature->value }}] {{ $g->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Party Classification *</label>
                        <select name="party_type" class="form-select" required>
                            <option value="none">General / Nominal (Income/Expense)</option>
                            <option value="customer">Customer (Sundry Debtor)</option>
                            <option value="supplier">Supplier (Sundry Creditor)</option>
                            <option value="bank">Bank Account</option>
                            <option value="cash">Cash Account</option>
                            <option value="employee">Employee</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Opening Balance</label>
                        <input type="number" step="0.01" name="opening_balance" class="form-control" value="{{ old('opening_balance', '0.00') }}" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Dr / Cr</label>
                        <select name="opening_balance_type" class="form-select">
                            <option value="Dr">Dr (Debit)</option>
                            <option value="Cr">Cr (Credit)</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Default GST Rate</label>
                        <select name="tax_master_id" class="form-select">
                            <option value="">-- No Tax / Not Applicable --</option>
                            @foreach($taxes as $t)
                                <option value="{{ $t->id }}">{{ $t->name }} ({{ $t->rate }}%)</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12"><hr class="my-2"><h6 class="fw-bold small text-muted text-uppercase mb-0">Tax & Contact Details (Optional)</h6></div>

                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">GSTIN</label>
                        <input type="text" name="gstin" class="form-control" value="{{ old('gstin') }}" placeholder="15-character GSTIN">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">PAN Number</label>
                        <input type="text" name="pan" class="form-control" value="{{ old('pan') }}" placeholder="10-digit PAN">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">State</label>
                        <input type="text" name="state" class="form-control" value="{{ old('state', $company->state) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">State Code</label>
                        <input type="text" name="state_code" class="form-control" value="{{ old('state_code', $company->state_code) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Credit Limit (₹)</label>
                        <input type="number" step="0.01" name="credit_limit" class="form-control" value="{{ old('credit_limit', '0.00') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Credit Days</label>
                        <input type="number" name="credit_days" class="form-control" value="{{ old('credit_days', 0) }}">
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                    <a href="{{ route('ledgers.index') }}" class="btn btn-light border px-4">Cancel</a>
                    <button type="submit" class="btn btn-primary px-4 fw-semibold">Save Ledger</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
