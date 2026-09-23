@extends('layouts.app')

@section('title', 'New Purchase Bill')

@section('content')
<div x-data="purchaseForm()" class="pb-5">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="fa-solid fa-cart-shopping text-warning me-2"></i> New Vendor Purchase Bill (F9)</h4>
            <p class="text-muted small mb-0">Records supplier invoice, increases warehouse stock, and logs Purchase Voucher</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('purchases.index') }}" class="btn btn-sm btn-outline-secondary">Cancel</a>
            <button type="button" class="btn btn-primary btn-sm fw-semibold px-4" @click="submitForm()">
                <i class="fa-solid fa-check me-1"></i> Save Purchase Bill (Ctrl+S)
            </button>
        </div>
    </div>

    <form id="purchaseForm" class="keyboard-save-form" action="{{ route('purchases.store') }}" method="POST">
        @csrf
        <!-- Supplier & Bill Meta -->
        <div class="card card-modern p-4 mb-4">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Supplier / Vendor *</label>
                    <select name="supplier_ledger_id" class="form-select" required>
                        <option value="">-- Choose Supplier --</option>
                        @foreach($suppliers as $s)
                            <option value="{{ $s->id }}" {{ old('supplier_ledger_id') == $s->id ? 'selected' : '' }}>
                                {{ $s->name }} ({{ $s->state }}) [GST: {{ $s->gstin ?: 'None' }}]
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Vendor Bill # *</label>
                    <input type="text" name="bill_no" class="form-control fw-bold" value="{{ old('bill_no') }}" required placeholder="e.g. INV-9901">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Bill Date *</label>
                    <input type="date" name="bill_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Payment Due Date</label>
                    <input type="date" name="due_date" class="form-control" value="{{ date('Y-m-d', strtotime('+30 days')) }}">
                </div>
            </div>
        </div>

        <!-- Line Items -->
        <div class="card card-modern p-4 mb-4">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h6 class="fw-bold mb-0">Purchased Goods & Raw Materials</h6>
                <button type="button" class="btn btn-sm btn-outline-primary" @click="addItem()">
                    <i class="fa-solid fa-plus me-1"></i> Add Line
                </button>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-light small text-uppercase">
                        <tr>
                            <th style="min-width: 260px;">Item / Product *</th>
                            <th style="width: 110px;">HSN Code</th>
                            <th style="width: 100px;" class="text-end">Qty *</th>
                            <th style="width: 130px;" class="text-end">Unit Rate (₹) *</th>
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
                                        <option value="">-- Choose Product / Item --</option>
                                        @foreach($products as $p)
                                            <option value="{{ $p->id }}"
                                                    data-name="{{ $p->name }}"
                                                    data-hsn="{{ $p->hsn_code }}"
                                                    data-price="{{ $p->purchase_price }}"
                                                    data-tax="{{ $p->taxMaster->rate ?? 0 }}">
                                                {{ $p->name }} (Buying: ₹ {{ number_format($p->purchase_price, 2) }})
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

            <!-- Tax Summary -->
            <div class="row justify-content-end mt-3">
                <div class="col-md-5">
                    <div class="bg-light p-3 rounded-3 small">
                        <div class="d-flex justify-content-between py-1">
                            <span class="text-muted">Taxable Subtotal:</span>
                            <span class="fw-semibold num-align" x-text="'₹ ' + formatNumber(taxableAmount)"></span>
                        </div>
                        <div class="d-flex justify-content-between py-1 text-warning-emphasis">
                            <span>Input Tax Credit (ITC):</span>
                            <span class="fw-semibold num-align" x-text="'₹ ' + formatNumber(totalTax)"></span>
                        </div>
                        <div class="d-flex justify-content-between py-2 border-top border-bottom fs-5 fw-bold text-main mt-2">
                            <span>Total Bill Amount:</span>
                            <span class="text-warning-emphasis num-align" x-text="'₹ ' + formatNumber(grandTotal)"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2">
            <a href="{{ route('purchases.index') }}" class="btn btn-light border px-4">Cancel</a>
            <button type="submit" class="btn btn-primary px-5 fw-semibold py-2">
                <i class="fa-solid fa-check me-1"></i> Save & Post Purchase (Ctrl+S)
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function purchaseForm() {
    return {
        items: [
            { product_id: '', description: '', hsn_code: '', quantity: 1, unit_price: 0, discount_amount: 0, gst_rate: 18, tax_amount: 0, total_amount: 0 }
        ],
        get taxableAmount() {
            return this.items.reduce((sum, item) => {
                const sub = (parseFloat(item.quantity || 0) * parseFloat(item.unit_price || 0)) - parseFloat(item.discount_amount || 0);
                return sum + Math.max(0, sub);
            }, 0);
        },
        get totalTax() {
            return this.items.reduce((sum, item) => sum + parseFloat(item.tax_amount || 0), 0);
        },
        get grandTotal() {
            return Math.round(this.taxableAmount + this.totalTax);
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
        submitForm() {
            document.getElementById('purchaseForm').submit();
        }
    };
}
</script>
@endpush
