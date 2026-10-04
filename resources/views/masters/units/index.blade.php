@extends('layouts.app')

@section('title', 'Base Measurement Units')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-0">Base Measurement Units</h4>
        <span class="text-muted small">Manage units of measure for inventory items, billing, and packaging</span>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('products.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-boxes-stacked me-1"></i> Products List
        </a>
        <a href="{{ route('uqc.index') }}" class="btn btn-outline-info btn-sm">
            <i class="fa-solid fa-barcode me-1"></i> GST UQC Codes
        </a>
        <button type="button" class="btn btn-primary btn-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#createUnitModal">
            <i class="fa-solid fa-plus me-1"></i> Add Unit
        </button>
    </div>
</div>

<div class="card card-modern shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;" class="text-center">#</th>
                        <th>Unit Name</th>
                        <th class="text-center">Symbol / UQC</th>
                        <th class="text-center">Decimal Precision</th>
                        <th class="text-center">Assigned Items</th>
                        <th style="width: 140px;" class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($units as $idx => $unit)
                        <tr>
                            <td class="text-center text-muted">{{ $units->firstItem() + $idx }}</td>
                            <td>
                                <div class="fw-bold text-main">
                                    <i class="fa-solid fa-scale-unbalanced text-success me-2"></i>
                                    {{ $unit->name }}
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-primary text-white font-monospace px-2 py-1 fw-bold">
                                    {{ $unit->symbol }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="text-muted small">{{ $unit->decimal_places }} decimals</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-secondary-subtle text-secondary px-2 py-1">
                                    {{ $unit->products_count }} item(s)
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editUnitModal{{ $unit->id }}" title="Edit">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>
                                    <form action="{{ route('units.destroy', $unit->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete unit \'{{ $unit->name }}\'?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Delete" {{ $unit->products_count > 0 ? 'disabled' : '' }}>
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </div>

                                <!-- Edit Modal -->
                                <div class="modal fade text-start" id="editUnitModal{{ $unit->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <form action="{{ route('units.update', $unit->id) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-header">
                                                    <h5 class="modal-title fw-bold">Edit Measurement Unit</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <div class="d-flex align-items-center justify-content-between mb-1">
                                                            <label class="form-label small fw-semibold mb-0">Symbol / Code (GST UQC) *</label>
                                                            <a href="{{ route('uqc.index') }}" target="_blank" class="small text-decoration-none text-muted" title="Manage UQC Codes">
                                                                <i class="fa-solid fa-gear me-1"></i> Manage UQC
                                                            </a>
                                                        </div>
                                                        <select name="symbol" class="form-select font-monospace" required onchange="handleUnitUqcChange(this, 'edit_unit_name_{{ $unit->id }}')">
                                                            <option value="">-- Select UQC Code --</option>
                                                            @foreach($uqcs as $uqc)
                                                                <option value="{{ $uqc->code }}" data-name="{{ $uqc->name }}" {{ strtoupper($unit->symbol) === $uqc->code ? 'selected' : '' }}>
                                                                    {{ $uqc->code }} - {{ $uqc->name }}
                                                                </option>
                                                            @endforeach
                                                            @if(!$uqcs->contains('code', strtoupper($unit->symbol)))
                                                                <option value="{{ $unit->symbol }}" selected>{{ $unit->symbol }} (Custom)</option>
                                                            @endif
                                                        </select>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-semibold">Unit Name *</label>
                                                        <input type="text" name="name" id="edit_unit_name_{{ $unit->id }}" class="form-control" value="{{ old('name', $unit->name) }}" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-semibold">Decimal Places</label>
                                                        <select name="decimal_places" class="form-select">
                                                            <option value="0" {{ $unit->decimal_places == 0 ? 'selected' : '' }}>0 (Whole numbers e.g. 1, 2, 3)</option>
                                                            <option value="1" {{ $unit->decimal_places == 1 ? 'selected' : '' }}>1 decimal (e.g. 1.5)</option>
                                                            <option value="2" {{ $unit->decimal_places == 2 ? 'selected' : '' }}>2 decimals (e.g. 1.25)</option>
                                                            <option value="3" {{ $unit->decimal_places == 3 ? 'selected' : '' }}>3 decimals (e.g. 1.250)</option>
                                                        </select>
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
                            <td colspan="6" class="text-center py-4 text-muted">
                                <i class="fa-solid fa-scale-unbalanced fs-2 d-block mb-2 text-muted opacity-50"></i>
                                No measurement units found. Click "Add Unit" to create one.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($units->hasPages())
        <div class="card-footer bg-white border-0 py-3">
            {{ $units->links() }}
        </div>
    @endif
</div>

<!-- Create Modal -->
<div class="modal fade" id="createUnitModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('units.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="fa-solid fa-scale-unbalanced text-success me-2"></i> Add Measurement Unit</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <label class="form-label small fw-semibold mb-0">Symbol / Code (GST UQC) *</label>
                            <a href="{{ route('uqc.index') }}" target="_blank" class="small text-decoration-none text-muted" title="Manage UQC Codes">
                                <i class="fa-solid fa-gear me-1"></i> Manage UQC
                            </a>
                        </div>
                        <select name="symbol" id="new_unit_symbol" class="form-select font-monospace" required onchange="handleUnitUqcChange(this, 'new_unit_name')">
                            <option value="">-- Select UQC Code (e.g. NOS, KGS, PCS) --</option>
                            @foreach($uqcs as $uqc)
                                <option value="{{ $uqc->code }}" data-name="{{ $uqc->name }}">{{ $uqc->code }} - {{ $uqc->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Unit Name *</label>
                        <input type="text" name="name" id="new_unit_name" class="form-control" placeholder="e.g. Numbers, Kilograms, Sets" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Decimal Places</label>
                        <select name="decimal_places" class="form-select">
                            <option value="0">0 (Whole numbers e.g. 1, 2, 3)</option>
                            <option value="1">1 decimal (e.g. 1.5)</option>
                            <option value="2">2 decimals (e.g. 1.25)</option>
                            <option value="3">3 decimals (e.g. 1.250)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-semibold">Save Unit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function handleUnitUqcChange(selectEl, targetNameInputId) {
    const selectedOpt = selectEl.options[selectEl.selectedIndex];
    if (selectedOpt && selectedOpt.value) {
        const uqcName = selectedOpt.getAttribute('data-name');
        const nameInput = document.getElementById(targetNameInputId);
        if (nameInput && (!nameInput.value || nameInput.value.trim() === '')) {
            nameInput.value = uqcName;
        }
    }
}
</script>
@endsection
