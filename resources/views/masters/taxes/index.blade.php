@extends('layouts.app')

@section('title', 'Applicable GST Rates')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-0">Applicable GST Rates (Tax Master)</h4>
        <span class="text-muted small">Manage GST tax slabs, rates, and component allocations for billing and purchases</span>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('products.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-boxes-stacked me-1"></i> Products List
        </a>
        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createTaxModal">
            <i class="fa-solid fa-plus me-1"></i> Add GST Rate
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
                        <th>Tax Name</th>
                        <th class="text-center">Total GST Rate</th>
                        <th class="text-center">CGST %</th>
                        <th class="text-center">SGST %</th>
                        <th class="text-center">IGST %</th>
                        <th class="text-center">Cess %</th>
                        <th class="text-center">Assigned Items</th>
                        <th style="width: 140px;" class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($taxes as $idx => $tax)
                        <tr>
                            <td class="text-center text-muted">{{ $taxes->firstItem() + $idx }}</td>
                            <td>
                                <div class="fw-bold text-main">
                                    <i class="fa-solid fa-percent text-warning me-2"></i>
                                    {{ $tax->name }}
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-primary text-white px-2 py-1 fs-6 fw-bold">
                                    {{ (float)$tax->rate }}%
                                </span>
                            </td>
                            <td class="text-center">{{ (float)$tax->cgst_rate }}%</td>
                            <td class="text-center">{{ (float)$tax->sgst_rate }}%</td>
                            <td class="text-center fw-semibold text-primary">{{ (float)$tax->igst_rate }}%</td>
                            <td class="text-center">{{ (float)$tax->cess_rate > 0 ? (float)$tax->cess_rate . '%' : '-' }}</td>
                            <td class="text-center">
                                <span class="badge bg-secondary-subtle text-secondary px-2 py-1">
                                    {{ $tax->products_count }} item(s)
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editTaxModal{{ $tax->id }}" title="Edit">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>
                                    <form action="{{ route('taxes.destroy', $tax->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete \'{{ $tax->name }}\'?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Delete" {{ ($tax->products_count > 0 || $tax->ledgers_count > 0) ? 'disabled' : '' }}>
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </div>

                                <!-- Edit Modal -->
                                <div class="modal fade text-start" id="editTaxModal{{ $tax->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form action="{{ route('taxes.update', $tax->id) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-header">
                                                    <h5 class="modal-title fw-bold">Edit GST Tax Rate</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-semibold">Tax Display Name *</label>
                                                        <input type="text" name="name" class="form-control" value="{{ old('name', $tax->name) }}" required>
                                                    </div>
                                                    <div class="row g-2 mb-3">
                                                        <div class="col-6">
                                                            <label class="form-label small fw-semibold">Total GST Rate (%) *</label>
                                                            <input type="number" step="0.01" name="rate" class="form-control" id="edit_tax_rate_{{ $tax->id }}" value="{{ old('rate', $tax->rate) }}" required oninput="autoCalcEditTax({{ $tax->id }})">
                                                        </div>
                                                        <div class="col-6">
                                                            <label class="form-label small fw-semibold">Cess Rate (%)</label>
                                                            <input type="number" step="0.01" name="cess_rate" class="form-control" value="{{ old('cess_rate', $tax->cess_rate) }}">
                                                        </div>
                                                    </div>
                                                    <div class="row g-2 mb-3 bg-light p-2 rounded">
                                                        <div class="col-4">
                                                            <label class="form-label small text-muted">CGST (%)</label>
                                                            <input type="number" step="0.01" name="cgst_rate" class="form-control form-control-sm" id="edit_cgst_{{ $tax->id }}" value="{{ old('cgst_rate', $tax->cgst_rate) }}">
                                                        </div>
                                                        <div class="col-4">
                                                            <label class="form-label small text-muted">SGST (%)</label>
                                                            <input type="number" step="0.01" name="sgst_rate" class="form-control form-control-sm" id="edit_sgst_{{ $tax->id }}" value="{{ old('sgst_rate', $tax->sgst_rate) }}">
                                                        </div>
                                                        <div class="col-4">
                                                            <label class="form-label small text-muted">IGST (%)</label>
                                                            <input type="number" step="0.01" name="igst_rate" class="form-control form-control-sm" id="edit_igst_{{ $tax->id }}" value="{{ old('igst_rate', $tax->igst_rate) }}">
                                                        </div>
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
                            <td colspan="9" class="text-center py-4 text-muted">
                                <i class="fa-solid fa-percent fs-2 d-block mb-2 text-muted opacity-50"></i>
                                No GST rates found. Click "Add GST Rate" to create one.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($taxes->hasPages())
        <div class="card-footer bg-white border-0 py-3">
            {{ $taxes->links() }}
        </div>
    @endif
</div>

<!-- Create Modal -->
<div class="modal fade" id="createTaxModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('taxes.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="fa-solid fa-percent text-warning me-2"></i> Add GST Tax Rate</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Tax Name *</label>
                        <input type="text" name="name" class="form-control" id="new_tax_name" placeholder="e.g. GST 28% or GST 12%" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Total GST Rate (%) *</label>
                            <input type="number" step="0.01" name="rate" class="form-control" id="new_tax_rate" placeholder="e.g. 28" required oninput="autoCalcNewTax()">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Cess Rate (%)</label>
                            <input type="number" step="0.01" name="cess_rate" class="form-control" value="0.00">
                        </div>
                    </div>
                    <div class="row g-2 mb-3 bg-light p-2 rounded">
                        <div class="col-4">
                            <label class="form-label small text-muted">CGST (%)</label>
                            <input type="number" step="0.01" name="cgst_rate" class="form-control form-control-sm" id="new_cgst" placeholder="14">
                        </div>
                        <div class="col-4">
                            <label class="form-label small text-muted">SGST (%)</label>
                            <input type="number" step="0.01" name="sgst_rate" class="form-control form-control-sm" id="new_sgst" placeholder="14">
                        </div>
                        <div class="col-4">
                            <label class="form-label small text-muted">IGST (%)</label>
                            <input type="number" step="0.01" name="igst_rate" class="form-control form-control-sm" id="new_igst" placeholder="28">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-semibold">Save Tax Rate</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function autoCalcNewTax() {
    const rate = parseFloat(document.getElementById('new_tax_rate').value) || 0;
    document.getElementById('new_cgst').value = (rate / 2).toFixed(2);
    document.getElementById('new_sgst').value = (rate / 2).toFixed(2);
    document.getElementById('new_igst').value = rate.toFixed(2);
    const nameField = document.getElementById('new_tax_name');
    if (!nameField.value || nameField.value.startsWith('GST ')) {
        nameField.value = 'GST ' + rate + '%';
    }
}

function autoCalcEditTax(id) {
    const rate = parseFloat(document.getElementById('edit_tax_rate_' + id).value) || 0;
    document.getElementById('edit_cgst_' + id).value = (rate / 2).toFixed(2);
    document.getElementById('edit_sgst_' + id).value = (rate / 2).toFixed(2);
    document.getElementById('edit_igst_' + id).value = rate.toFixed(2);
}
</script>
@endsection
