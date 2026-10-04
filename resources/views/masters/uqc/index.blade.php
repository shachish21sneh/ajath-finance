@extends('layouts.app')

@section('title', 'GST UQC Codes')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1">
            <i class="fa-solid fa-barcode text-primary me-2"></i> GST Unique Quantity Codes (UQC)
        </h4>
        <span class="text-muted small">Official Indian GST standard measurement unit codes (GSTR-1, e-Way Bill, e-Invoice)</span>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('units.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-scale-unbalanced me-1"></i> Units of Measure
        </a>
        <button type="button" class="btn btn-primary btn-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#createUqcModal">
            <i class="fa-solid fa-plus me-1"></i> Add UQC Code
        </button>
    </div>
</div>

<!-- Search & Stats Bar -->
<div class="card card-modern p-3 mb-4 shadow-sm border-0">
    <form action="{{ route('uqc.index') }}" method="GET" class="row g-2 align-items-center">
        <div class="col-md-5">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                <input type="text" name="search" class="form-control border-start-0" placeholder="Search by UQC code (e.g. NOS, KGS) or name..." value="{{ request('search') }}">
            </div>
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-sm btn-primary px-3">Search</button>
            @if(request('search'))
                <a href="{{ route('uqc.index') }}" class="btn btn-sm btn-light border ms-1">Clear</a>
            @endif
        </div>
        <div class="col text-end d-none d-md-block">
            <span class="text-muted small">Total: <strong>{{ $uqcs->total() }}</strong> standard codes</span>
        </div>
    </form>
</div>

<div class="card card-modern shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 60px;" class="text-center">#</th>
                        <th style="width: 160px;">UQC Code</th>
                        <th>Standard Unit Name</th>
                        <th class="text-center" style="width: 150px;">Assigned Units</th>
                        <th class="text-center" style="width: 140px;">Scope</th>
                        <th class="text-center" style="width: 100px;">Status</th>
                        <th style="width: 130px;" class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($uqcs as $idx => $uqc)
                        <tr>
                            <td class="text-center text-muted small">{{ $uqcs->firstItem() + $idx }}</td>
                            <td>
                                <span class="badge bg-primary text-white font-monospace px-2 py-1 fs-6 fw-bold">
                                    {{ $uqc->code }}
                                </span>
                            </td>
                            <td>
                                <span class="fw-semibold text-main">{{ $uqc->name }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-secondary-subtle text-secondary px-2 py-1">
                                    {{ $uqc->units_count }} unit(s)
                                </span>
                            </td>
                            <td class="text-center">
                                @if($uqc->company_id === null)
                                    <span class="badge bg-info-subtle text-info border px-2 py-1">
                                        <i class="fa-solid fa-building-columns me-1"></i> GST Standard
                                    </span>
                                @else
                                    <span class="badge bg-light text-dark border px-2 py-1">
                                        <i class="fa-solid fa-user me-1"></i> Custom
                                    </span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($uqc->is_active)
                                    <span class="badge bg-success-subtle text-success px-2 py-1">Active</span>
                                @else
                                    <span class="badge bg-secondary px-2 py-1">Inactive</span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editUqcModal{{ $uqc->id }}" title="Edit">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>
                                    <form action="{{ route('uqc.destroy', $uqc->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete UQC \'{{ $uqc->code }}\'?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Delete" {{ $uqc->units_count > 0 ? 'disabled' : '' }}>
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </div>

                                <!-- Edit Modal -->
                                <div class="modal fade text-start" id="editUqcModal{{ $uqc->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <form action="{{ route('uqc.update', $uqc->id) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-header">
                                                    <h5 class="modal-title fw-bold">Edit UQC Code</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-semibold">UQC Code (Symbol) *</label>
                                                        <input type="text" name="code" class="form-control text-uppercase font-monospace" value="{{ old('code', $uqc->code) }}" maxlength="20" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-semibold">Unit Name *</label>
                                                        <input type="text" name="name" class="form-control" value="{{ old('name', $uqc->name) }}" required>
                                                    </div>
                                                    <div class="form-check form-switch mt-2">
                                                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="activeSwitch{{ $uqc->id }}" {{ $uqc->is_active ? 'checked' : '' }}>
                                                        <label class="form-check-label small fw-semibold" for="activeSwitch{{ $uqc->id }}">Active for billing & inventory</label>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-primary fw-semibold">Save Changes</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-barcode fs-2 d-block mb-2 text-muted opacity-50"></i>
                                No UQC codes found matching your search.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($uqcs->hasPages())
        <div class="card-footer bg-white border-0 py-3">
            {{ $uqcs->links() }}
        </div>
    @endif
</div>

<!-- Create Modal -->
<div class="modal fade" id="createUqcModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('uqc.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="fa-solid fa-plus-circle text-primary me-2"></i> Add GST UQC Code</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">UQC Code (Symbol / Abbr) *</label>
                        <input type="text" name="code" class="form-control text-uppercase font-monospace" placeholder="e.g. NOS, KGS, PCS" maxlength="20" required>
                        <span class="text-muted small">Standard GST reporting code (usually 3 uppercase letters)</span>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Unit Name *</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Numbers, Kilograms, Pieces" required>
                    </div>
                    <div class="form-check form-switch mt-2">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="createActiveSwitch" checked>
                        <label class="form-check-label small fw-semibold" for="createActiveSwitch">Active for billing & inventory</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-semibold">Save UQC Code</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
