@extends('layouts.app')

@section('title', 'Create Sales Invoice')

@section('content')
<div x-data="invoiceForm()" class="pb-5">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="fa-solid fa-file-invoice-dollar text-primary me-2"></i> New GST Sales Tax Invoice (F8)</h4>
            <p class="text-muted small mb-0">Generates legal tax invoice, deducts warehouse inventory, and posts Sales Voucher</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('sales.index') }}" class="btn btn-sm btn-outline-secondary">Cancel</a>
            <button type="button" class="btn btn-primary btn-sm fw-semibold px-4" @click="submitForm()">
                <i class="fa-solid fa-check me-1"></i> Save & Generate Invoice (Ctrl+S)
            </button>
        </div>
    </div>

    <form id="salesInvoiceForm" class="keyboard-save-form" action="{{ route('sales.store') }}" method="POST">
        @csrf
        <!-- Invoice Details Header -->
        <div class="card card-modern p-4 mb-4">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Customer / Client *</label>
                    <select name="customer_ledger_id" class="form-select" x-model="customerId" @change="onCustomerChange()" required>
                        <option value="">-- Choose Customer --</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}" data-state-code="{{ $c->state_code }}" data-gstin="{{ $c->gstin }}">
                                {{ $c->name }} ({{ $c->state }}) [GST: {{ $c->gstin ?: 'None' }}]
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Invoice Number</label>
                    <input type="text" name="invoice_no" class="form-control fw-bold bg-light" value="{{ old('invoice_no', $invoiceNo) }}" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Invoice Date *</label>
                    <input type="date" name="invoice_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Payment Terms / Due Date</label>
                    <input type="date" name="due_date" class="form-control" value="{{ date('Y-m-d', strtotime('+15 days')) }}">
                </div>
            </div>
        </div>

        <!-- Line Items Table -->
        <div class="card card-modern p-4 mb-4">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h6 class="fw-bold mb-0">Invoice Line Items & Products</h6>
                <button type="button" class="btn btn-sm btn-outline-primary" @click="addItem()">
                    <i class="fa-solid fa-plus me-1"></i> Add Product Line
                </button>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-light small text-uppercase">
                        <tr>
                            <th style="min-width: 260px;">Item / Product *</th>
                            <th style="width: 110px;">HSN/SAC</th>
                            <th style="width: 100px;" class="text-end">Qty *</th>
                            <th style="width: 130px;" class="text-end">Rate (₹) *</th>
                            <th style="width: 100px;" class="text-end">Disc (₹)</th>
                            <th style="width: 110px;">GST %</th>
                            <th style="width: 140px;" class="text-end">Tax (₹)</th>
                            <th style="width: 150px;" class="text-end">Total (₹)</th>
                            <th style="width: 40px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(item, index) in items" :key="index">
                            <tr>
                                <td>
                                    <select :name="`items[${index}][product_id]`" class="form-select form-select-sm" x-model="item.product_id" @change="onProductChange(index)">
                                        <option value="">-- Select Product / Custom --</option>
                                        @foreach($products as $p)
                                            <option value="{{ $p->id }}"
                                                    data-name="{{ $p->name }}"
                                                    data-hsn="{{ $p->hsn_code ?: $p->sac_code }}"
                                                    data-price="{{ $p->selling_price }}"
                                                    data-tax="{{ $p->taxMaster->rate ?? 0 }}">
                                                {{ $p->name }} (₹ {{ number_format($p->selling_price, 2) }}) [Stock: {{ $p->current_stock }}]
                                            </option>
                                        @endforeach
                                    </select>
                                    <input type="text" :name="`items[${index}][description]`" class="form-control form-control-sm mt-1" x-model="item.description" placeholder="Description..." required>
                                </td>
                                <td>
                                    <input type="text" :name="`items[${index}][hsn_code]`" class="form-control form-control-sm" x-model="item.hsn_code">
                                </td>
                                <td>
                                    <input type="number" step="0.01" min="0.01" :name="`items[${index}][quantity]`" class="form-control form-control-sm text-end" x-model="item.quantity" @input="recalcRow(index)" required>
                                </td>
                                <td>
                                    <input type="number" step="0.01" min="0" :name="`items[${index}][unit_price]`" class="form-control form-control-sm text-end" x-model="item.unit_price" @input="recalcRow(index)" required>
                                </td>
                                <td>
                                    <input type="number" step="0.01" min="0" :name="`items[${index}][discount_amount]`" class="form-control form-control-sm text-end" x-model="item.discount_amount" @input="recalcRow(index)">
                                </td>
                                <td>
                                    <select :name="`items[${index}][gst_rate]`" class="form-select form-select-sm" x-model="item.gst_rate" @change="recalcRow(index)">
                                        <option value="0">0%</option>
                                        <option value="5">5%</option>
                                        <option value="12">12%</option>
                                        <option value="18">18%</option>
                                        <option value="28">28%</option>
                                    </select>
                                </td>
                                <td class="text-end small num-align fw-semibold text-muted" x-text="'₹ ' + formatNumber(item.tax_amount)"></td>
                                <td class="text-end fw-bold num-align" x-text="'₹ ' + formatNumber(item.total_amount)"></td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm text-danger p-0" @click="removeItem(index)" x-show="items.length > 1">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <!-- Tax Summary & Calculation Panel -->
            <div class="row justify-content-end mt-3">
                <div class="col-md-5">
                    <div class="bg-light p-3 rounded-3 small">
                        <div class="d-flex justify-content-between py-1">
                            <span class="text-muted">Subtotal (Gross):</span>
                            <span class="fw-semibold num-align" x-text="'₹ ' + formatNumber(subtotal)"></span>
                        </div>
                        <div class="d-flex justify-content-between py-1">
                            <span class="text-muted">Taxable Amount:</span>
                            <span class="fw-semibold num-align" x-text="'₹ ' + formatNumber(taxableAmount)"></span>
                        </div>
                        <div class="d-flex justify-content-between py-1 text-primary">
                            <span>Total GST Output:</span>
                            <span class="fw-semibold num-align" x-text="'₹ ' + formatNumber(totalTax)"></span>
                        </div>
                        <div class="d-flex justify-content-between py-1 text-muted">
                            <span>Round Off:</span>
                            <span class="num-align" x-text="'₹ ' + formatNumber(roundOff)"></span>
                        </div>
                        <div class="d-flex justify-content-between py-2 border-top border-bottom fs-5 fw-bold text-main mt-2">
                            <span>Grand Total:</span>
                            <span class="text-primary num-align" x-text="'₹ ' + formatNumber(grandTotal)"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Settlement & Terms Card -->
        <div class="card card-modern p-4 mb-4">
            <h6 class="fw-bold mb-3">Payment Settlement & Notes</h6>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Payment Method</label>
                    <select name="payment_method" class="form-select">
                        <option value="cash">Cash</option>
                        <option value="bank_transfer">Bank Transfer / NEFT</option>
                        <option value="upi">UPI / Online</option>
                        <option value="credit">Credit (On Account)</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Amount Received / Paid Now (₹)</label>
                    <input type="number" step="0.01" name="paid_amount" class="form-control" value="0.00">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Internal Notes</label>
                    <input type="text" name="notes" class="form-control" placeholder="Order memo, dispatch note...">
                </div>
                <div class="col-12">
                    <label class="form-label small fw-semibold">Invoice Terms & Conditions</label>
                    <textarea name="terms_conditions" class="form-control" rows="2">1. Goods once sold will not be taken back.
2. Subject to Delhi jurisdiction only. Interest @ 18% p.a. applicable on overdue bills.</textarea>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2">
            <a href="{{ route('sales.index') }}" class="btn btn-light border px-4">Cancel</a>
            <button type="submit" class="btn btn-primary px-5 fw-semibold py-2">
                <i class="fa-solid fa-check me-1"></i> Save & Generate Tax Invoice (Ctrl+S)
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function invoiceForm() {
    return {
        customerId: '',
        items: [
            { product_id: '', description: '', hsn_code: '', quantity: 1, unit_price: 0, discount_amount: 0, gst_rate: 18, tax_amount: 0, total_amount: 0 }
        ],
        get subtotal() {
            return this.items.reduce((sum, item) => sum + (parseFloat(item.quantity || 0) * parseFloat(item.unit_price || 0)), 0);
        },
        get taxableAmount() {
            return this.items.reduce((sum, item) => {
                const sub = (parseFloat(item.quantity || 0) * parseFloat(item.unit_price || 0)) - parseFloat(item.discount_amount || 0);
                return sum + Math.max(0, sub);
            }, 0);
        },
        get totalTax() {
            return this.items.reduce((sum, item) => sum + parseFloat(item.tax_amount || 0), 0);
        },
        get rawGrandTotal() {
            return this.taxableAmount + this.totalTax;
        },
        get grandTotal() {
            return Math.round(this.rawGrandTotal);
        },
        get roundOff() {
            return Math.round((this.grandTotal - this.rawGrandTotal) * 100) / 100;
        },
        formatNumber(num) {
            return (num || 0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        },
        addItem() {
            this.items.push({
                product_id: '',
                description: '',
                hsn_code: '',
                quantity: 1,
                unit_price: 0,
                discount_amount: 0,
                gst_rate: 18,
                tax_amount: 0,
                total_amount: 0
            });
        },
        removeItem(index) {
            if (this.items.length > 1) {
                this.items.splice(index, 1);
            }
        },
        onProductChange(index) {
            const select = event.target;
            const opt = select.selectedOptions[0];
            if (opt && opt.value) {
                this.items[index].description = opt.getAttribute('data-name');
                this.items[index].hsn_code = opt.getAttribute('data-hsn') || '';
                this.items[index].unit_price = parseFloat(opt.getAttribute('data-price')) || 0;
                this.items[index].gst_rate = parseFloat(opt.getAttribute('data-tax')) || 0;
                this.recalcRow(index);
            }
        },
        recalcRow(index) {
            const item = this.items[index];
            const qty = parseFloat(item.quantity) || 0;
            const price = parseFloat(item.unit_price) || 0;
            const disc = parseFloat(item.discount_amount) || 0;
            const taxable = Math.max(0, (qty * price) - disc);
            const rate = parseFloat(item.gst_rate) || 0;

            item.tax_amount = Math.round(((taxable * rate) / 100) * 100) / 100;
            item.total_amount = Math.round((taxable + item.tax_amount) * 100) / 100;
        },
        onCustomerChange() {
            // Customer selection trigger
        },
        submitForm() {
            document.getElementById('salesInvoiceForm').submit();
        }
    };
}
</script>
@endpush
