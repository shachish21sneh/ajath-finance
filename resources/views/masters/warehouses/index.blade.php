@extends('layouts.app')

@section('title', 'Godowns & Warehouses')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-1">Godowns & Storage Depots</h4>
        <p class="text-muted small mb-0">Multi-warehouse stock locations for inventory allocation and inter-depot transfers</p>
    </div>
    <button type="button" class="btn btn-primary btn-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#newWarehouseModal">
        <i class="fa-solid fa-plus me-1"></i> Add Godown
    </button>
</div>

<div class="row g-4">
    @foreach($warehouses as $wh)
        <div class="col-md-6 col-xl-4">
            <div class="card card-modern p-4 h-100 position-relative {{ $wh->is_default ? 'border-primary border-2' : '' }}">
                @if($wh->is_default)
                    <span class="position-absolute top-0 end-0 m-3 badge bg-primary">Default Depot</span>
                @endif
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="bg-primary-subtle text-primary rounded-3 p-3 fs-4">
                        <i class="fa-solid fa-warehouse"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-0">{{ $wh->name }}</h5>
                        <div class="text-muted small">Code: {{ $wh->code ?: 'N/A' }}</div>
                    </div>
                </div>
                <p class="text-muted small mb-0"><i class="fa-solid fa-location-dot me-1"></i> {{ $wh->address ?: 'No physical address configured.' }}</p>
            </div>
        </div>
    @endforeach
</div>

<!-- Add Warehouse Modal -->
<div class="modal fade" id="newWarehouseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content card-modern">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fs-6 fw-bold"><i class="fa-solid fa-warehouse me-2 text-primary"></i> Add Warehouse / Godown</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('warehouses.store') }}" method="POST">
                @csrf
                <div class="modal-body p-3">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Godown Name *</label>
                        <input type="text" name="name" class="form-control" required placeholder="e.g. South Depot, Plant 2">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Location Code</label>
                        <input type="text" name="code" class="form-control" placeholder="e.g. WH-02">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Address</label>
                        <textarea name="address" class="form-control" rows="2" placeholder="Address..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-semibold">Save Godown</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
