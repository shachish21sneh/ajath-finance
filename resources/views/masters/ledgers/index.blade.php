@extends('layouts.app')

@section('title', 'Chart of Accounts')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-3">
    <div>
        <h4 class="fw-bold mb-1">Chart of Accounts & General Ledgers</h4>
        <p class="text-muted small mb-0">Master accounts for <strong>{{ $company->name ?? 'Default Company' }}</strong></p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('ledgers.create') }}" class="btn btn-primary btn-sm fw-semibold">
            <i class="fa-solid fa-plus me-1"></i> New Ledger
        </a>
    </div>
</div>

<!-- Type Filter Buttons -->
<div class="d-flex gap-2 mb-3 overflow-x-auto pb-1">
    <a href="{{ route('ledgers.index') }}" class="btn btn-sm {{ !request()->has('party_type') || request('party_type') === 'all' ? 'btn-primary' : 'btn-light border' }}">All Ledgers</a>
    <a href="{{ route('ledgers.index', ['party_type' => 'customer']) }}" class="btn btn-sm {{ request('party_type') === 'customer' ? 'btn-primary' : 'btn-light border' }}">Customers (Debtors)</a>
    <a href="{{ route('ledgers.index', ['party_type' => 'supplier']) }}" class="btn btn-sm {{ request('party_type') === 'supplier' ? 'btn-primary' : 'btn-light border' }}">Suppliers (Creditors)</a>
    <a href="{{ route('ledgers.index', ['party_type' => 'bank']) }}" class="btn btn-sm {{ request('party_type') === 'bank' ? 'btn-primary' : 'btn-light border' }}">Bank Accounts</a>
    <a href="{{ route('ledgers.index', ['party_type' => 'cash']) }}" class="btn btn-sm {{ request('party_type') === 'cash' ? 'btn-primary' : 'btn-light border' }}">Cash Accounts</a>
    <a href="{{ route('ledgers.index', ['party_type' => 'none']) }}" class="btn btn-sm {{ request('party_type') === 'none' ? 'btn-primary' : 'btn-light border' }}">Incomes & Expenses</a>
</div>

<div class="card card-modern p-4">
    <div class="table-responsive">
        <table id="ledgersTable" class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr class="small text-muted text-uppercase">
                    <th>Ledger Name</th>
                    <th>Code</th>
                    <th>Parent Group</th>
                    <th>Nature</th>
                    <th>Party Type</th>
                    <th class="text-end">Current Balance</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($ledgers as $l)
                    <tr>
                        <td>
                            <a href="{{ route('ledgers.statement', $l->id) }}" class="fw-bold text-decoration-none text-main">
                                {{ $l->name }}
                            </a>
                            @if($l->is_system)
                                <span class="badge bg-light text-muted border ms-1 small" style="font-size: 0.65rem;">System</span>
                            @endif
                        </td>
                        <td class="small text-muted">{{ $l->code ?: '-' }}</td>
                        <td class="small">{{ $l->group->name ?? 'Primary' }}</td>
                        <td>
                            @php
                                $nature = $l->group?->nature?->value ?? 'ASSET';
                                $badge = match($nature) {
                                    'ASSET' => 'bg-info-subtle text-info-emphasis',
                                    'LIABILITY' => 'bg-warning-subtle text-warning-emphasis',
                                    'INCOME' => 'bg-success-subtle text-success',
                                    'EXPENSE' => 'bg-danger-subtle text-danger',
                                    default => 'bg-secondary',
                                };
                            @endphp
                            <span class="badge {{ $badge }}">{{ $nature }}</span>
                        </td>
                        <td>
                            <span class="badge bg-light text-muted border text-capitalize">{{ $l->party_type->value }}</span>
                        </td>
                        <td class="text-end fw-bold num-align">
                            <span class="{{ (float)$l->current_balance >= 0 ? 'text-primary' : 'text-danger' }}">
                                {{ $l->formatted_balance }}
                            </span>
                        </td>
                        <td class="text-center">
                            <a href="{{ route('ledgers.statement', $l->id) }}" class="btn btn-sm btn-light border py-1 px-2" title="Account Statement">
                                <i class="fa-solid fa-file-invoice text-primary"></i>
                            </a>
                            <a href="{{ route('ledgers.edit', $l->id) }}" class="btn btn-sm btn-light border py-1 px-2" title="Edit ledger">
                                <i class="fa-solid fa-pen text-muted"></i>
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#ledgersTable').DataTable({
        pageLength: 25,
        order: [[0, 'asc']]
    });
});
</script>
@endpush
