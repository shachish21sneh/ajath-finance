@extends('layouts.app')

@section('title', 'Add New User')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card card-modern p-4">
            <div class="d-flex align-items-center justify-content-between mb-4 border-bottom pb-3">
                <h5 class="fw-bold mb-0"><i class="fa-solid fa-user-plus me-2 text-primary"></i> Create System User</h5>
                <a href="{{ route('users.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fa-solid fa-arrow-left me-1"></i> Back
                </a>
            </div>

            <form action="{{ route('users.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Full Name *</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required placeholder="e.g. John Doe">
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Email Address *</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email') }}" required placeholder="user@company.com">
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Initial Password *</label>
                    <input type="password" name="password" class="form-control" required placeholder="Minimum 6 characters">
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Security Role *</label>
                    <select name="role_id" class="form-select" required>
                        @foreach($roles as $r)
                            <option value="{{ $r->id }}" {{ old('role_id') == $r->id ? 'selected' : '' }}>{{ $r->name }} ({{ $r->slug }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Default Assigned Company</label>
                    <select name="company_id" class="form-select">
                        <option value="">-- All Companies --</option>
                        @foreach($companies as $c)
                            <option value="{{ $c->id }}" {{ old('company_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Phone Number</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone') }}" placeholder="+91...">
                </div>
                <div class="form-check mb-4">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" class="form-check-input" id="is_active" checked>
                    <label class="form-check-label small" for="is_active">User account is active</label>
                </div>

                <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                    <a href="{{ route('users.index') }}" class="btn btn-light border px-4">Cancel</a>
                    <button type="submit" class="btn btn-primary px-4 fw-semibold">Save User</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
