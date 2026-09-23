@extends('layouts.app')

@section('title', 'Banking & Cash Books')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-1">Banking & Cash Books</h4>
        <p class="text-muted small mb-0">Live bank balances, cash reserves, and atomic ledger transaction feed</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('vouchers.create', ['type' => 'CONTRA']) }}" class="btn btn-info btn-sm fw-bold text-dark">
            <i class="fa-solid fa-arrow-right-arrow-left me-1"></i> Contra Entry (F4)
        </a>
    </div>
</div>

<!-- Bank & Cash Account Cards -->
<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="card card-modern p-4">
            <h6 class="fw-bold mb-3"><i class="fa-solid fa-building-columns text-info me-2"></i> Bank Accounts</h6>
            <div class="list-group list-group-flush">
                @forelse($bankLedgers as $bank)
                    <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-3">
                        <div>
                            <a href="{{ route('ledgers.statement', $bank->id) }}" class="fw-bold text-decoration-none text-main">
                                {{ $bank->name }}
                            </a>
                            <div class="text-muted small">
                                A/c: {{ $bank->bank_account_no ?: 'Current A/c' }} &bull; IFSC: {{ $bank->bank_ifsc ?: 'N/A' }}
                            </div>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold fs-6 text-primary num-align">{{ $bank->formatted_balance }}</div>
                            <a href="{{ route('ledgers.statement', $bank->id) }}" class="btn btn-sm btn-light border py-0 px-2 mt-1 small">
                                Passbook
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="text-muted py-3">No bank accounts configured.</div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card card-modern p-4">
            <h6 class="fw-bold mb-3"><i class="fa-solid fa-money-bill-wave text-success me-2"></i> Cash in Hand Books</h6>
            <div class="list-group list-group-flush">
                @forelse($cashLedgers as $cash)
                    <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-3">
                        <div>
                            <a href="{{ route('ledgers.statement', $cash->id) }}" class="fw-bold text-decoration-none text-main">
                                {{ $cash->name }}
                            </a>
                            <div class="text-muted small">Physical Cash Register</div>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold fs-6 text-success num-align">{{ $cash->formatted_balance }}</div>
                            <a href="{{ route('ledgers.statement', $cash->id) }}" class="btn btn-sm btn-light border py-0 px-2 mt-1 small">
                                Cash Book
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="text-muted py-3">No cash ledgers found.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Recent Bank / Cash Flow Table -->
<div class="card card-modern p-4">
    <h6 class="fw-bold mb-3"><i class="fa-solid fa-clock-rotate-left text-primary me-2"></i> Recent Cash & Bank Flows</h6>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light small text-uppercase">
                <tr>
                    <th>Date</th>
                    <th>Account</th>
                    <th>Voucher #</th>
                    <th>Type</th>
                    <th>Particulars</th>
                    <th class="text-end">Inward (Deposit)</th>
                    <th class="text-end">Outward (Withdrawal)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentEntries as $entry)
                    @php $isDr = $entry->entry_type->value === 'debit'; @endphp
                    <tr>
                        <td class="small">{{ $entry->entry_date->format('d-M-Y') }}</td>
                        <td class="fw-bold small">{{ $entry->ledger->name }}</td>
                        <td class="small fw-semibold">
                            <a href="{{ route('vouchers.show', $entry->voucher_id) }}" class="text-decoration-none">
                                {{ $entry->voucher->voucher_no ?? '-' }}
                            </a>
                        </td>
                        <td>
                            <span class="badge {{ $entry->voucher->voucher_type->badgeClass() ?? 'bg-secondary' }}">
                                {{ $entry->voucher->voucher_type->value ?? '-' }}
                            </span>
                        </td>
                        <td class="small text-muted">{{ $entry->narration ?: '-' }}</td>
                        <td class="text-end fw-bold num-align text-success">
                            {{ $isDr ? '₹ ' . number_format($entry->amount, 2) : '-' }}
                        </td>
                        <td class="text-end fw-bold num-align text-danger">
                            {{ !$isDr ? '₹ ' . number_format($entry->amount, 2) : '-' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">No recent banking entries.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
