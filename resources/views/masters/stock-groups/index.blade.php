@extends('layouts.app')

@section('title', 'Stock Groups')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-0">Stock Groups</h4>
        <span class="text-muted small">Manage inventory hierarchy, categories, and product groupings</span>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('products.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-boxes-stacked me-1"></i> Products List
        </a>
        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createStockGroupModal">
            <i class="fa-solid fa-plus me-1"></i> Add Stock Group
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
                        <th>Group Name</th>
                        <th>Parent Group</th>
                        <th>HSN / SAC</th>
                        <th>GST Rate</th>
                        <th class="text-center">Assigned Items</th>
                        <th style="width: 140px;" class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stockGroups as $idx => $group)
                        <tr>
                            <td class="text-center text-muted">{{ $stockGroups->firstItem() + $idx }}</td>
                            <td>
                                <div class="fw-bold text-main">
                                    <i class="fa-solid fa-layer-group text-primary me-2"></i>
                                    {{ $group->name }}
                                </div>
                            </td>
                            <td>
                                @if($group->parent)
                                    <span class="badge bg-light text-dark border">{{ $group->parent->name }}</span>
                                @else
                                    <span class="text-muted small">-- Primary Group --</span>
                                @endif
                            </td>
                            <td>
                                @if($group->hsn_code)
                                    <span class="badge bg-light text-dark border font-monospace">{{ $group->hsn_code }}</span>
                                @else
                                    <span class="text-muted small">--</span>
                                @endif
                            </td>
                            <td>
                                @if($group->taxMaster)
                                    <span class="badge bg-primary-subtle text-primary border fw-semibold">
                                        {{ $group->taxMaster->name }} ({{ (float)$group->taxMaster->rate }}%)
                                    </span>
                                @else
                                    <span class="text-muted small">--</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge bg-secondary-subtle text-secondary px-2 py-1">
                                    {{ $group->products_count }} item(s)
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editGroupModal{{ $group->id }}" title="Edit">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>
                                    <form action="{{ route('stock-groups.destroy', $group->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete \'{{ $group->name }}\'?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Delete" {{ $group->products_count > 0 ? 'disabled' : '' }}>
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </div>

                                <!-- Edit Modal -->
                                <div class="modal fade text-start" id="editGroupModal{{ $group->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form action="{{ route('stock-groups.update', $group->id) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-header">
                                                    <h5 class="modal-title fw-bold">Edit Stock Group</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-semibold">Group Name *</label>
                                                        <input type="text" name="name" class="form-control" value="{{ old('name', $group->name) }}" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-semibold">Parent Group</label>
                                                        <select name="parent_id" class="form-select">
                                                            <option value="">-- None (Primary) --</option>
                                                            @foreach($parentGroups as $pg)
                                                                @if($pg->id !== $group->id)
                                                                    <option value="{{ $pg->id }}" {{ $group->parent_id == $pg->id ? 'selected' : '' }}>{{ $pg->name }}</option>
                                                                @endif
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="row g-2 mb-2">
                                                        <div class="col-6">
                                                            <label class="form-label small fw-semibold">HSN / SAC Code</label>
                                                            <input type="text" name="hsn_code" class="form-control" value="{{ old('hsn_code', $group->hsn_code) }}" placeholder="e.g. 850720">
                                                        </div>
                                                        <div class="col-6">
                                                            <label class="form-label small fw-semibold">Applicable GST Rate</label>
                                                            <select name="tax_master_id" class="form-select">
                                                                <option value="">-- None / Default --</option>
                                                                @foreach($taxes as $t)
                                                                    <option value="{{ $t->id }}" {{ $group->tax_master_id == $t->id ? 'selected' : '' }}>{{ $t->name }} ({{ $t->rate }}%)</option>
                                                                @endforeach
                                                            </select>
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
                            <td colspan="7" class="text-center py-4 text-muted">
                                <i class="fa-solid fa-layer-group fs-2 d-block mb-2 text-muted opacity-50"></i>
                                No stock groups found. Click "Add Stock Group" to create one.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($stockGroups->hasPages())
        <div class="card-footer bg-white border-0 py-3">
            {{ $stockGroups->links() }}
        </div>
    @endif
</div>

<!-- Create Modal -->
<div class="modal fade" id="createStockGroupModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('stock-groups.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="fa-solid fa-layer-group text-primary me-2"></i> Add Stock Group</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Group Name *</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Energy Storage & Batteries" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Parent Group</label>
                        <select name="parent_id" class="form-select">
                            <option value="">-- None (Primary) --</option>
                            @foreach($parentGroups as $pg)
                                <option value="{{ $pg->id }}">{{ $pg->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">HSN / SAC Code</label>
                            <input type="text" name="hsn_code" class="form-control" placeholder="e.g. 850720">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Applicable GST Rate</label>
                            <select name="tax_master_id" class="form-select">
                                <option value="">-- None / Default --</option>
                                @foreach($taxes as $t)
                                    <option value="{{ $t->id }}">{{ $t->name }} ({{ $t->rate }}%)</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-semibold">Save Group</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
