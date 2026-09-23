@extends('layouts.app')

@section('title', 'Financial Years')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-1">Financial Year Management</h4>
        <p class="text-muted small mb-0">Accounting periods for <strong>{{ $company->name ?? 'Default Company' }}</strong> (Hotkey: F3 to switch)</p>
    </div>
    <button type="button" class="btn btn-primary btn-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#newFyModal">
        <i class="fa-solid fa-calendar-plus me-1"></i> New Financial Year
    </button>
</div>

<div class="card card-modern p-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr class="small text-muted text-uppercase">
                    <th>Title</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Status</th>
                    <th>Period State</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($financialYears as $fy)
                    <tr>
                        <td class="fw-bold">{{ $fy->title }}</td>
                        <td>{{ $fy->start_date->format('d-M-Y') }}</td>
                        <td>{{ $fy->end_date->format('d-M-Y') }}</td>
                        <td>
                            @if($currentFinancialYear?->id == $fy->id)
                                <span class="badge bg-primary"><i class="fa-solid fa-check me-1"></i> Current Active</span>
                            @else
                                <span class="badge bg-light text-muted border">Inactive</span>
                            @endif
                        </td>
                        <td>
                            @if($fy->is_locked)
                                <span class="badge bg-danger-subtle text-danger"><i class="fa-solid fa-lock me-1"></i> Locked</span>
                            @else
                                <span class="badge bg-success-subtle text-success"><i class="fa-solid fa-lock-open me-1"></i> Open for entries</span>
                            @endif
                        </td>
                        <td class="text-end">
                            @if($currentFinancialYear?->id != $fy->id)
                                <form action="{{ route('financial-years.switch', $fy->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-primary py-1 px-2">
                                        <i class="fa-solid fa-right-to-bracket me-1"></i> Set Current
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">No financial years configured.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- New FY Modal -->
<div class="modal fade" id="newFyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content card-modern">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fs-6 fw-bold"><i class="fa-solid fa-calendar-plus me-2 text-primary"></i> Create Financial Year</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('financial-years.store') }}" method="POST">
                @csrf
                <div class="modal-body p-3">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Title *</label>
                        <input type="text" name="title" class="form-control" required placeholder="e.g. FY 2026-2027">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Start Date *</label>
                        <input type="date" name="start_date" class="form-control" required value="{{ date('Y') }}-04-01">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">End Date *</label>
                        <input type="date" name="end_date" class="form-control" required value="{{ (int)date('Y')+1 }}-03-31">
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-semibold">Create & Set Active</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
