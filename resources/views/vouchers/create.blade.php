@extends('layouts.app')

@section('title', 'New ' . $voucherType->label())

@section('content')
<div x-data="voucherEntry()" class="pb-5">
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-3 gap-3">
        <div class="d-flex align-items-center gap-3">
            <span class="badge {{ $voucherType->badgeClass() }} fs-6 px-3 py-2">
                {{ $voucherType->value }} ({{ $voucherType->shortcut() }})
            </span>
            <h4 class="fw-bold mb-0">{{ $voucherType->label() }}</h4>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="text-muted small d-none d-md-inline"><kbd>Ctrl+S</kbd> to save anytime</span>
            <button type="button" class="btn btn-primary fw-semibold px-4" :disabled="!isBalanced" @click="submitVoucher()">
                <i class="fa-solid fa-floppy-disk me-1"></i> Post Voucher (Ctrl+S)
            </button>
        </div>
    </div>

    <!-- Quick Voucher Switcher Strip -->
    <div class="d-flex gap-2 mb-3 overflow-x-auto pb-1 no-print">
        <a href="{{ route('vouchers.create', ['type' => 'CONTRA']) }}" class="btn btn-sm {{ $voucherType->value === 'CONTRA' ? 'btn-info text-dark fw-bold' : 'btn-light border' }}">F4 Contra</a>
        <a href="{{ route('vouchers.create', ['type' => 'PAYMENT']) }}" class="btn btn-sm {{ $voucherType->value === 'PAYMENT' ? 'btn-danger text-white fw-bold' : 'btn-light border' }}">F5 Payment</a>
        <a href="{{ route('vouchers.create', ['type' => 'RECEIPT']) }}" class="btn btn-sm {{ $voucherType->value === 'RECEIPT' ? 'btn-success text-white fw-bold' : 'btn-light border' }}">F6 Receipt</a>
        <a href="{{ route('vouchers.create', ['type' => 'JOURNAL']) }}" class="btn btn-sm {{ $voucherType->value === 'JOURNAL' ? 'btn-secondary text-white fw-bold' : 'btn-light border' }}">F7 Journal</a>
        <a href="{{ route('vouchers.create', ['type' => 'DEBIT_NOTE']) }}" class="btn btn-sm {{ $voucherType->value === 'DEBIT_NOTE' ? 'btn-warning text-dark fw-bold' : 'btn-light border' }}">Debit Note</a>
        <a href="{{ route('vouchers.create', ['type' => 'CREDIT_NOTE']) }}" class="btn btn-sm {{ $voucherType->value === 'CREDIT_NOTE' ? 'btn-warning text-dark fw-bold' : 'btn-light border' }}">Credit Note</a>
    </div>

    <form id="voucherForm" class="keyboard-save-form" action="{{ route('vouchers.store') }}" method="POST">
        @csrf
        <input type="hidden" name="voucher_type" value="{{ $voucherType->value }}">

        <!-- Header Info Card -->
        <div class="card card-modern p-4 mb-4">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold text-muted">Voucher Number</label>
                    <input type="text" name="voucher_no" class="form-control fw-bold bg-light" value="{{ old('voucher_no', $voucherNo) }}" required readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Voucher Date *</label>
                    <input type="date" name="voucher_date" class="form-control" value="{{ old('voucher_date', date('Y-m-d')) }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Payment Mode</label>
                    <select name="payment_mode" class="form-select" x-model="paymentMode">
                        <option value="cash">Cash</option>
                        <option value="bank">Bank / Net Banking</option>
                        <option value="cheque">Cheque</option>
                        <option value="upi">UPI / QR</option>
                        <option value="credit">Credit / Journal</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Reference # / Bill Ref</label>
                    <input type="text" name="reference_no" class="form-control" value="{{ old('reference_no') }}" placeholder="e.g. Inv #, Cheque #">
                </div>

                <div class="col-md-6" x-show="paymentMode === 'cheque'" style="display: none;">
                    <label class="form-label small fw-semibold">Cheque Number</label>
                    <input type="text" name="cheque_no" class="form-control" placeholder="6-digit cheque number">
                </div>
                <div class="col-md-6" x-show="paymentMode === 'cheque'" style="display: none;">
                    <label class="form-label small fw-semibold">Cheque Date</label>
                    <input type="date" name="cheque_date" class="form-control" value="{{ date('Y-m-d') }}">
                </div>
            </div>
        </div>

        <!-- Multi-row Entry Grid -->
        <div class="card card-modern p-4 mb-4">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h6 class="fw-bold mb-0"><i class="fa-solid fa-list-check me-2 text-primary"></i> Multi-Ledger Debit & Credit Lines</h6>
                <button type="button" class="btn btn-sm btn-outline-primary btn-add-grid-row" @click="addRow()">
                    <i class="fa-solid fa-plus me-1"></i> Add Line (Enter)
                </button>
            </div>

            <div class="table-responsive">
                <table class="voucher-grid-table">
                    <thead>
                        <tr>
                            <th style="width: 110px;">Type (Dr/Cr)</th>
                            <th style="min-width: 250px;">Account Ledger *</th>
                            <th style="width: 170px;" class="text-end">Debit (₹)</th>
                            <th style="width: 170px;" class="text-end">Credit (₹)</th>
                            <th>Line Particulars / Narration</th>
                            <th style="width: 50px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(row, index) in rows" :key="index">
                            <tr>
                                <td>
                                    <select :name="`items[${index}][entry_type]`" class="voucher-input grid-nav-input fw-bold" x-model="row.entry_type" @change="onTypeChange(index)">
                                        <option value="debit">Dr</option>
                                        <option value="credit">Cr</option>
                                    </select>
                                </td>
                                <td>
                                    <select :name="`items[${index}][ledger_id]`" class="voucher-input grid-nav-input" x-model="row.ledger_id" required>
                                        <option value="">-- Choose Ledger --</option>
                                        @foreach($ledgers as $l)
                                            <option value="{{ $l->id }}">{{ $l->name }} ({{ $l->group->name ?? '' }}) [{{ $l->formatted_balance }}]</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="number" step="0.01" min="0" :name="`items[${index}][amount]`" class="voucher-input grid-nav-input text-end fw-semibold text-danger"
                                           x-model="row.debit_amount"
                                           :disabled="row.entry_type !== 'debit'"
                                           @input="onDebitInput(index)"
                                           placeholder="0.00">
                                </td>
                                <td>
                                    <input type="number" step="0.01" min="0" :name="`items[${index}][amount]`" class="voucher-input grid-nav-input text-end fw-semibold text-success"
                                           x-model="row.credit_amount"
                                           :disabled="row.entry_type !== 'credit'"
                                           @input="onCreditInput(index)"
                                           placeholder="0.00">
                                </td>
                                <td>
                                    <input type="text" :name="`items[${index}][narration]`" class="voucher-input grid-nav-input" x-model="row.narration" placeholder="Row memo...">
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm text-danger p-0" @click="removeRow(index)" x-show="rows.length > 2">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <!-- Double Entry Totals & Invariant Verification Bar -->
            <div class="voucher-balance-bar mt-4">
                <div class="d-flex align-items-center gap-4">
                    <div>
                        <span class="text-muted small d-block">TOTAL DEBITS</span>
                        <span class="fs-5 fw-bold text-danger num-align" x-text="'₹ ' + formatNumber(totalDebit)">₹ 0.00</span>
                    </div>
                    <div>
                        <span class="text-muted small d-block">TOTAL CREDITS</span>
                        <span class="fs-5 fw-bold text-success num-align" x-text="'₹ ' + formatNumber(totalCredit)">₹ 0.00</span>
                    </div>
                    <div x-show="!isBalanced">
                        <span class="text-muted small d-block">DIFFERENCE</span>
                        <span class="fs-5 fw-bold text-danger num-align" x-text="'₹ ' + formatNumber(difference)">₹ 0.00</span>
                    </div>
                </div>

                <div>
                    <template x-if="isBalanced">
                        <span class="balance-status-badge balanced">
                            <i class="fa-solid fa-circle-check me-1"></i> DOUBLE ENTRY BALANCED
                        </span>
                    </template>
                    <template x-if="!isBalanced">
                        <span class="balance-status-badge unbalanced">
                            <i class="fa-solid fa-triangle-exclamation me-1"></i> UNBALANCED (Diff: ₹ <span x-text="formatNumber(difference)"></span>)
                        </span>
                    </template>
                </div>
            </div>
        </div>

        <!-- Overall Narration Card -->
        <div class="card card-modern p-4 mb-4">
            <label class="form-label small fw-semibold">Master Narration / Transaction Memo</label>
            <textarea name="narration" class="form-control" rows="2" placeholder="Overall description of the financial transaction..."></textarea>
        </div>

        <!-- Sticky Footer Action Bar -->
        <div class="d-flex justify-content-between align-items-center bg-card-bg p-3 border rounded-3 shadow-sm">
            <a href="{{ route('vouchers.index') }}" class="btn btn-light border px-4">Cancel</a>
            <div class="d-flex align-items-center gap-3">
                <span x-show="!isBalanced" class="text-danger small fw-semibold">
                    <i class="fa-solid fa-circle-exclamation me-1"></i> Debits must equal Credits to save.
                </span>
                <button type="submit" class="btn btn-primary px-5 py-2 fw-semibold" :disabled="!isBalanced">
                    <i class="fa-solid fa-check me-1"></i> Post & Balance Voucher (Ctrl+S)
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function voucherEntry() {
    return {
        paymentMode: 'cash',
        rows: [
            { entry_type: 'debit', ledger_id: '', debit_amount: '', credit_amount: '', narration: '' },
            { entry_type: 'credit', ledger_id: '', debit_amount: '', credit_amount: '', narration: '' }
        ],
        get totalDebit() {
            return this.rows.reduce((sum, r) => sum + (parseFloat(r.debit_amount) || 0), 0);
        },
        get totalCredit() {
            return this.rows.reduce((sum, r) => sum + (parseFloat(r.credit_amount) || 0), 0);
        },
        get difference() {
            return Math.abs(Math.round((this.totalDebit - this.totalCredit) * 100) / 100);
        },
        get isBalanced() {
            const d = Math.round(this.totalDebit * 100) / 100;
            const c = Math.round(this.totalCredit * 100) / 100;
            return d > 0 && d === c;
        },
        formatNumber(num) {
            return (num || 0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        },
        addRow() {
            // Smart auto-entry type balancing
            const nextType = this.totalDebit > this.totalCredit ? 'credit' : 'debit';
            const diff = this.difference > 0 ? this.difference.toFixed(2) : '';
            this.rows.push({
                entry_type: nextType,
                ledger_id: '',
                debit_amount: nextType === 'debit' ? diff : '',
                credit_amount: nextType === 'credit' ? diff : '',
                narration: ''
            });
        },
        removeRow(index) {
            if (this.rows.length > 2) {
                this.rows.splice(index, 1);
            }
        },
        onTypeChange(index) {
            const row = this.rows[index];
            if (row.entry_type === 'debit') {
                row.debit_amount = row.credit_amount;
                row.credit_amount = '';
            } else {
                row.credit_amount = row.debit_amount;
                row.debit_amount = '';
            }
        },
        onDebitInput(index) {
            this.rows[index].credit_amount = '';
            this.autoBalanceOpposite(index, 'debit');
        },
        onCreditInput(index) {
            this.rows[index].debit_amount = '';
            this.autoBalanceOpposite(index, 'credit');
        },
        autoBalanceOpposite(index, inputType) {
            // If only 2 rows and editing row 0, auto-suggest the matching amount in row 1
            if (this.rows.length === 2 && index === 0) {
                const val = inputType === 'debit' ? this.rows[0].debit_amount : this.rows[0].credit_amount;
                if (inputType === 'debit' && this.rows[1].entry_type === 'credit' && !this.rows[1].credit_amount) {
                    this.rows[1].credit_amount = val;
                } else if (inputType === 'credit' && this.rows[1].entry_type === 'debit' && !this.rows[1].debit_amount) {
                    this.rows[1].debit_amount = val;
                }
            }
        },
        submitVoucher() {
            if (!this.isBalanced) {
                alert('Cannot post: Total Debits must match Total Credits.');
                return;
            }
            document.getElementById('voucherForm').submit();
        }
    };
}
</script>
@endpush
