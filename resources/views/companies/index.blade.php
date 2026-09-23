@extends('layouts.app')

@section('title', 'Company Management')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-1">Company Management</h4>
        <p class="text-muted small mb-0">Manage multiple business entities and financial entities (Hotkey: F2 to switch)</p>
    </div>
    <a href="{{ route('companies.create') }}" class="btn btn-primary btn-sm fw-semibold">
        <i class="fa-solid fa-plus me-1"></i> New Company
    </a>
</div>

<div class="row g-4">
    @foreach($companies as $c)
        <div class="col-md-6 col-xl-4">
            <div class="card card-modern p-4 h-100 position-relative {{ ($currentCompany?->id == $c->id) ? 'border-primary border-2' : '' }}">
                @if($currentCompany?->id == $c->id)
                    <span class="position-absolute top-0 end-0 m-3 badge bg-primary">
                        <i class="fa-solid fa-check me-1"></i> Active Workspace
                    </span>
                @endif
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="bg-primary-subtle text-primary rounded-3 p-3 fs-4">
                        <i class="fa-solid fa-building"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-0">{{ $c->name }}</h5>
                        <div class="text-muted small">{{ $c->legal_name ?: $c->name }}</div>
                    </div>
                </div>

                <div class="small mb-3">
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span class="text-muted">GSTIN:</span>
                        <span class="fw-semibold">{{ $c->gstin ?: 'Not Registered' }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span class="text-muted">State:</span>
                        <span class="fw-semibold">{{ $c->state }} ({{ $c->state_code }})</span>
                    </div>
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span class="text-muted">Financial Years:</span>
                        <span class="fw-semibold">{{ $c->financialYears->count() }} active</span>
                    </div>
                </div>

                <div class="mt-auto d-flex gap-2">
                    @if($currentCompany?->id != $c->id)
                        <form action="{{ route('companies.switch', $c->id) }}" method="POST" class="flex-grow-1">
                            @csrf
                            <button type="submit" class="btn btn-outline-primary btn-sm w-100 fw-semibold">
                                <i class="fa-solid fa-right-to-bracket me-1"></i> Switch To
                            </button>
                        </form>
                    @endif
                    <a href="{{ route('companies.edit', $c->id) }}" class="btn btn-light border btn-sm fw-semibold">
                        <i class="fa-solid fa-pen-to-square me-1"></i> Edit
                    </a>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection
