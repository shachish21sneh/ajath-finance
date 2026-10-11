@extends('layouts.app')

@section('title', 'All Vouchers')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-3">
    <div>
        <h4 class="fw-bold mb-1">Accounting Vouchers Register</h4>
        <p class="text-muted small mb-0">Browse all double-entry journal, contra, payment, receipt, sales, and purchase transactions</p>
    </div>
    <div class="d-flex gap-2">
        <div class="dropdown">
            <button class="btn btn-primary btn-sm dropdown-toggle fw-semibold" type="button" data-bs-toggle="dropdown">
                <i class="fa-solid fa-plus me-1"></i> New Voucher
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                <li><a class="dropdown-item" href="{{ route('vouchers.create', ['type' => 'CONTRA']) }}"><kbd class="me-2 text-info">F4</kbd> Contra Voucher</a></li>
                <li><a class="dropdown-item" href="{{ route('vouchers.create', ['type' => 'PAYMENT']) }}"><kbd class="me-2 text-danger">F5</kbd> Payment Voucher</a></li>
                <li><a class="dropdown-item" href="{{ route('vouchers.create', ['type' => 'RECEIPT']) }}"><kbd class="me-2 text-success">F6</kbd> Receipt Voucher</a></li>
                <li><a class="dropdown-item" href="{{ route('vouchers.create', ['type' => 'JOURNAL']) }}"><kbd class="me-2">F7</kbd> Journal Voucher</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="{{ route('sales.create') }}"><kbd class="me-2 text-primary">F8</kbd> Sales Invoice</a></li>
                <li><a class="dropdown-item" href="{{ route('purchases.create') }}"><kbd class="me-2 text-warning">F9</kbd> Purchase Invoice</a></li>
            </ul>
        </div>
    </div>
</div>

<!-- Type Filter Buttons -->
<div class="d-flex gap-2 mb-3 overflow-x-auto pb-1">
    <a href="{{ route('vouchers.index') }}" class="btn btn-sm {{ !request('type') ? 'btn-primary' : 'btn-light border' }}">All Types</a>
    <a href="{{ route('vouchers.index', ['type' => 'CONTRA']) }}" class="btn btn-sm {{ request('type') === 'CONTRA' ? 'btn-primary' : 'btn-light border' }}">Contra (F4)</a>
    <a href="{{ route('vouchers.index', ['type' => 'PAYMENT']) }}" class="btn btn-sm {{ request('type') === 'PAYMENT' ? 'btn-primary' : 'btn-light border' }}">Payment (F5)</a>
    <a href="{{ route('vouchers.index', ['type' => 'RECEIPT']) }}" class="btn btn-sm {{ request('type') === 'RECEIPT' ? 'btn-primary' : 'btn-light border' }}">Receipt (F6)</a>
    <a href="{{ route('vouchers.index', ['type' => 'JOURNAL']) }}" class="btn btn-sm {{ request('type') === 'JOURNAL' ? 'btn-primary' : 'btn-light border' }}">Journal (F7)</a>
    <a href="{{ route('vouchers.index', ['type' => 'SALES']) }}" class="btn btn-sm {{ request('type') === 'SALES' ? 'btn-primary' : 'btn-light border' }}">Sales (F8)</a>
    <a href="{{ route('vouchers.index', ['type' => 'PURCHASE']) }}" class="btn btn-sm {{ request('type') === 'PURCHASE' ? 'btn-primary' : 'btn-light border' }}">Purchase (F9)</a>
</div>

<div class="card card-modern p-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr class="small text-muted text-uppercase">
                    <th>Type</th>
                    <th>Voucher #</th>
                    <th>Date</th>
                    <th>Reference / Party</th>
                    <th>Narration</th>
                    <th class="text-end">Total Amount</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($vouchers as $v)
                    <tr>
                        <td>
                            <span class="badge {{ $v->voucher_type->badgeClass() }}">
                                {{ $v->voucher_type->value }}
                            </span>
                        </td>
                        <td class="fw-bold">{{ $v->voucher_no }}</td>
                        <td class="small">{{ $v->voucher_date->format('d-M-Y') }}</td>
                        <td class="small">
                            <span class="fw-medium">{{ $v->partyLedger->name ?? 'Multi-Ledger Entry' }}</span>
                            @if($v->reference_no)
                                <div class="text-muted" style="font-size: 0.72rem;">Ref: {{ $v->reference_no }}</div>
                            @endif
                        </td>
                        <td class="small text-muted text-truncate" style="max-width: 250px;">
                            {{ $v->narration ?: '-' }}
                        </td>
                        <td class="text-end fw-bold num-align">₹ {{ number_format($v->total_amount, 2) }}</td>
                        <td class="text-center">
                            <div class="d-inline-flex gap-1 align-items-center">
                                <a href="{{ route('vouchers.show', $v->id) }}" class="btn btn-sm btn-light border py-1 px-2" title="View Voucher">
                                    <i class="fa-solid fa-eye text-primary"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-light border py-1 px-2 text-warning-emphasis" data-bs-toggle="modal" data-bs-target="#editVoucherModal{{ $v->id }}" title="Edit Voucher">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                            </div>

                            <!-- Edit Voucher Modal -->
                            <div class="modal fade text-start" id="editVoucherModal{{ $v->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content card-modern">
                                        <div class="modal-header border-bottom">
                                            <h5 class="modal-title fs-6 fw-bold">
                                                <i class="fa-solid fa-pen-to-square me-2 text-primary"></i> Edit Voucher: {{ $v->voucher_no }}
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form action="{{ route('vouchers.update', $v->id) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-body p-3">
                                                <div class="row g-2 mb-3">
                                                    <div class="col-6">
                                                        <label class="form-label small fw-semibold">Voucher Date *</label>
                                                        <input type="date" name="voucher_date" class="form-control" required value="{{ old('voucher_date', $v->voucher_date->format('Y-m-d')) }}">
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="form-label small fw-semibold">Payment Mode</label>
                                                        <select name="payment_mode" class="form-select">
                                                            <option value="cash" {{ $v->payment_mode === 'cash' ? 'selected' : '' }}>Cash</option>
                                                            <option value="bank" {{ $v->payment_mode === 'bank' ? 'selected' : '' }}>Bank</option>
                                                            <option value="cheque" {{ $v->payment_mode === 'cheque' ? 'selected' : '' }}>Cheque</option>
                                                            <option value="upi" {{ $v->payment_mode === 'upi' ? 'selected' : '' }}>UPI / Digital</option>
                                                            <option value="credit" {{ $v->payment_mode === 'credit' ? 'selected' : '' }}>Credit / Accrual</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label small fw-semibold">Reference #</label>
                                                    <input type="text" name="reference_no" class="form-control" value="{{ old('reference_no', $v->reference_no) }}" placeholder="e.g. Bank Ref / Challan">
                                                </div>
                                                <div class="row g-2 mb-3">
                                                    <div class="col-6">
                                                        <label class="form-label small fw-semibold">Cheque / Txn #</label>
                                                        <input type="text" name="cheque_no" class="form-control" value="{{ old('cheque_no', $v->cheque_no) }}">
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="form-label small fw-semibold">Cheque Date</label>
                                                        <input type="date" name="cheque_date" class="form-control" value="{{ old('cheque_date', $v->cheque_date?->format('Y-m-d')) }}">
                                                    </div>
                                                </div>
                                                <div class="mb-2">
                                                    <label class="form-label small fw-semibold">Narration</label>
                                                    <textarea name="narration" class="form-control" rows="3" placeholder="Narration or reason for voucher...">{{ old('narration', $v->narration) }}</textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer border-top">
                                                <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-primary fw-semibold">Update Voucher</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">No vouchers found matching criteria.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="card-footer bg-white border-top py-3 mt-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span class="small text-muted">
            Showing {{ $vouchers->firstItem() ?? ($vouchers->total() > 0 ? 1 : 0) }} to {{ $vouchers->lastItem() ?? $vouchers->total() }} of {{ $vouchers->total() }} vouchers
        </span>
        <div>
            @if($vouchers->hasPages())
                {{ $vouchers->withQueryString()->links() }}
            @else
                <ul class="pagination pagination-sm mb-0">
                    <li class="page-item disabled"><span class="page-link">&laquo;</span></li>
                    <li class="page-item active"><span class="page-link">1</span></li>
                    <li class="page-item disabled"><span class="page-link">&raquo;</span></li>
                </ul>
            @endif
        </div>
    </div>
</div>
@endsection
