@extends('layouts.app')

@section('title', 'Suppliers Directory')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-1">Supplier Accounts (Sundry Creditors)</h4>
        <p class="text-muted small mb-0">Manage vendors, suppliers, GSTIN, and payable balances</p>
    </div>
    <a href="{{ route('suppliers.create') }}" class="btn btn-primary btn-sm fw-semibold">
        <i class="fa-solid fa-truck-field me-1"></i> New Supplier
    </a>
</div>

<div class="card card-modern p-4">
    <div class="table-responsive">
        <table id="suppliersTable" class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr class="small text-muted text-uppercase">
                    <th>Supplier Name</th>
                    <th>GSTIN / PAN</th>
                    <th>Contact Info</th>
                    <th>Location</th>
                    <th class="text-end">Balance Payable</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($suppliers as $s)
                    <tr>
                        <td>
                            <a href="{{ route('ledgers.statement', $s->id) }}" class="fw-bold text-decoration-none text-main">
                                {{ $s->name }}
                            </a>
                            <div class="text-muted small">Code: {{ $s->code ?: 'N/A' }}</div>
                        </td>
                        <td class="small">
                            <div><span class="text-muted">GST:</span> {{ $s->gstin ?: 'Unregistered' }}</div>
                            @if($s->pan) <div><span class="text-muted">PAN:</span> {{ $s->pan }}</div> @endif
                        </td>
                        <td class="small">
                            <div>{{ $s->phone ?: '-' }}</div>
                            <div class="text-muted">{{ $s->email ?: '-' }}</div>
                        </td>
                        <td class="small">
                            {{ $s->city ? $s->city . ', ' : '' }}{{ $s->state }} ({{ $s->state_code }})
                        </td>
                        <td class="text-end fw-bold num-align">
                            <span class="{{ (float)$s->current_balance < 0 ? 'text-danger' : 'text-success' }}">
                                {{ $s->formatted_balance }}
                            </span>
                        </td>
                        <td class="text-center">
                            <a href="{{ route('ledgers.statement', $s->id) }}" class="btn btn-sm btn-light border py-1 px-2" title="Supplier Statement">
                                <i class="fa-solid fa-file-lines text-primary"></i>
                            </a>
                            <a href="{{ route('suppliers.edit', $s->id) }}" class="btn btn-sm btn-light border py-1 px-2" title="Edit Supplier">
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
    $('#suppliersTable').DataTable({ pageLength: 25 });
});
</script>
@endpush
