@extends('layouts.app')

@section('title', 'Customers Directory')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-1">Customer Accounts (Sundry Debtors)</h4>
        <p class="text-muted small mb-0">Manage clients, GSTIN, credit limits, and outstanding balances</p>
    </div>
    <a href="{{ route('customers.create') }}" class="btn btn-primary btn-sm fw-semibold">
        <i class="fa-solid fa-user-plus me-1"></i> New Customer
    </a>
</div>

<div class="card card-modern p-4">
    <div class="table-responsive">
        <table id="customersTable" class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr class="small text-muted text-uppercase">
                    <th>Customer Name</th>
                    <th>GSTIN / PAN</th>
                    <th>Phone & Email</th>
                    <th>Location</th>
                    <th>Credit Limit</th>
                    <th class="text-end">Balance Due</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($customers as $c)
                    <tr>
                        <td>
                            <a href="{{ route('ledgers.statement', $c->id) }}" class="fw-bold text-decoration-none text-main">
                                {{ $c->name }}
                            </a>
                            <div class="text-muted small">Code: {{ $c->code ?: 'N/A' }}</div>
                        </td>
                        <td class="small">
                            <div><span class="text-muted">GST:</span> {{ $c->gstin ?: 'Unregistered' }}</div>
                            @if($c->pan) <div><span class="text-muted">PAN:</span> {{ $c->pan }}</div> @endif
                        </td>
                        <td class="small">
                            <div>{{ $c->phone ?: '-' }}</div>
                            <div class="text-muted">{{ $c->email ?: '-' }}</div>
                        </td>
                        <td class="small">
                            {{ $c->city ? $c->city . ', ' : '' }}{{ $c->state }} ({{ $c->state_code }})
                        </td>
                        <td class="small">
                            ₹ {{ number_format($c->credit_limit, 2) }}
                            <div class="text-muted" style="font-size: 0.72rem;">{{ $c->credit_days }} days</div>
                        </td>
                        <td class="text-end fw-bold num-align">
                            <span class="{{ (float)$c->current_balance > 0 ? 'text-danger' : 'text-success' }}">
                                {{ $c->formatted_balance }}
                            </span>
                        </td>
                        <td class="text-center">
                            <a href="{{ route('ledgers.statement', $c->id) }}" class="btn btn-sm btn-light border py-1 px-2" title="Customer Statement">
                                <i class="fa-solid fa-file-lines text-primary"></i>
                            </a>
                            <a href="{{ route('customers.edit', $c->id) }}" class="btn btn-sm btn-light border py-1 px-2" title="Edit Customer">
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
    $('#customersTable').DataTable({ pageLength: 25 });
});
</script>
@endpush
