@extends('layouts.app')

@section('title', 'Edit Ledger')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card card-modern p-4">
            <div class="d-flex align-items-center justify-content-between mb-4 border-bottom pb-3">
                <h5 class="fw-bold mb-0"><i class="fa-solid fa-pen-to-square me-2 text-primary"></i> Edit Ledger Account</h5>
                <a href="{{ route('ledgers.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fa-solid fa-arrow-left me-1"></i> Back
                </a>
            </div>

            <form action="{{ route('ledgers.update', $ledger->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label small fw-semibold">Ledger Name *</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $ledger->name) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Ledger Code / Alias</label>
                        <input type="text" name="code" class="form-control" value="{{ old('code', $ledger->code) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Parent Master Group *</label>
                        <select name="ledger_group_id" class="form-select" required>
                            @foreach($groups as $g)
                                <option value="{{ $g->id }}" {{ old('ledger_group_id', $ledger->ledger_group_id) == $g->id ? 'selected' : '' }}>
                                    [{{ $g->nature->value }}] {{ $g->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Party Classification *</label>
                        <select name="party_type" class="form-select" required>
                            <option value="none" {{ $ledger->party_type->value === 'none' ? 'selected' : '' }}>General / Nominal (Income/Expense)</option>
                            <option value="customer" {{ $ledger->party_type->value === 'customer' ? 'selected' : '' }}>Customer (Sundry Debtor)</option>
                            <option value="supplier" {{ $ledger->party_type->value === 'supplier' ? 'selected' : '' }}>Supplier (Sundry Creditor)</option>
                            <option value="bank" {{ $ledger->party_type->value === 'bank' ? 'selected' : '' }}>Bank Account</option>
                            <option value="cash" {{ $ledger->party_type->value === 'cash' ? 'selected' : '' }}>Cash Account</option>
                            <option value="employee" {{ $ledger->party_type->value === 'employee' ? 'selected' : '' }}>Employee</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Opening Balance</label>
                        <input type="number" step="0.01" name="opening_balance" class="form-control" value="{{ old('opening_balance', $ledger->opening_balance) }}" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Dr / Cr</label>
                        <select name="opening_balance_type" class="form-select">
                            <option value="Dr" {{ $ledger->opening_balance_type === 'Dr' ? 'selected' : '' }}>Dr (Debit)</option>
                            <option value="Cr" {{ $ledger->opening_balance_type === 'Cr' ? 'selected' : '' }}>Cr (Credit)</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Default GST Rate</label>
                        <select name="tax_master_id" class="form-select">
                            <option value="">-- No Tax / Not Applicable --</option>
                            @foreach($taxes as $t)
                                <option value="{{ $t->id }}" {{ $ledger->tax_master_id == $t->id ? 'selected' : '' }}>{{ $t->name }} ({{ $t->rate }}%)</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12"><hr class="my-2"><h6 class="fw-bold small text-muted text-uppercase mb-0">Tax & Contact Details</h6></div>

                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">GSTIN</label>
                        <input type="text" name="gstin" class="form-control" value="{{ old('gstin', $ledger->gstin) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">PAN Number</label>
                        <input type="text" name="pan" class="form-control" value="{{ old('pan', $ledger->pan) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">State</label>
                        <input type="text" name="state" class="form-control" value="{{ old('state', $ledger->state) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">State Code</label>
                        <input type="text" name="state_code" class="form-control" value="{{ old('state_code', $ledger->state_code) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Credit Limit (₹)</label>
                        <input type="number" step="0.01" name="credit_limit" class="form-control" value="{{ old('credit_limit', $ledger->credit_limit) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Credit Days</label>
                        <input type="number" name="credit_days" class="form-control" value="{{ old('credit_days', $ledger->credit_days) }}">
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                    <a href="{{ route('ledgers.index') }}" class="btn btn-light border px-4">Cancel</a>
                    <button type="submit" class="btn btn-primary px-4 fw-semibold">Update Ledger</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
