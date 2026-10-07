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

            <div class="table-responsive table-responsive-combobox" style="min-height: 280px;">
                <table class="table table-bordered align-middle">
                    <thead class="table-light small text-uppercase">
                        <tr>
                            <th style="min-width: 250px;">Item / Product *</th>
                            <th style="width: 100px;">HSN/SAC</th>
                            <th style="width: 80px;" class="text-end">Qty *</th>
                            <th style="width: 140px;" class="text-end">Rate (Incl. of Tax)</th>
                            <th style="width: 110px;" class="text-end">Rate (₹) *</th>
                            <th style="width: 90px;" class="text-end">Disc (₹)</th>
                            <th style="width: 95px;">GST %</th>
                            <th style="width: 115px;" class="text-end">Tax (₹)</th>
                            <th style="width: 130px;" class="text-end">Total (₹)</th>
                            <th style="width: 150px;">Godown</th>
                            <th style="width: 38px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(item, index) in items" :key="index">
                            <tr>
                                <td class="position-relative">
                                    <!-- Hidden input for product_id form submit -->
                                    <input type="hidden" :name="`items[${index}][product_id]`" :value="item.product_id">

                                    <!-- Product Combobox Input Trigger -->
                                    <div class="product-combobox-wrapper">
                                        <button type="button" 
                                                class="product-combobox-trigger"
                                                :class="{'is-active': item.isOpen, 'border-primary': item.product_id}"
                                                @click="openProductSearch(index)"
                                                :title="item.selectedLabel || '-- Select Product / Custom --'">
                                            <div class="d-flex align-items-center text-truncate me-2" style="min-width: 0; flex: 1 1 auto;">
                                                <span class="text-truncate" :class="item.selectedLabel ? 'fw-semibold text-dark' : 'text-muted'" x-text="item.selectedLabel || '-- Select Product / Custom --'"></span>
                                            </div>
                                            <div class="d-flex align-items-center flex-shrink-0 ms-auto gap-1">
                                                <span x-show="item.product_id || item.selectedLabel" 
                                                      @click.stop="clearProduct(index)" 
                                                      class="product-clear-btn" 
                                                      title="Clear selection">
                                                    <i class="fa-solid fa-xmark"></i>
                                                </span>
                                                <i class="fa-solid fa-chevron-down text-muted small opacity-75"></i>
                                            </div>
                                        </button>

                                        <!-- Floating AJAX Search Dropdown Menu -->
                                        <div x-show="item.isOpen" 
                                             @click.outside="closeProductSearch(index)"
                                             class="product-combobox-menu"
                                             x-cloak>
                                            
                                            <!-- Live Search Bar -->
                                            <div class="product-combobox-search-box">
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text bg-white border-end-0 text-muted ps-2.5">
                                                        <i class="fa-solid fa-magnifying-glass text-primary" x-show="!item.loading"></i>
                                                        <i class="fa-solid fa-circle-notch fa-spin text-primary" x-show="item.loading"></i>
                                                    </span>
                                                    <input type="text" 
                                                           :id="`product-search-input-${index}`"
                                                           class="form-control product-combobox-search-input border-start-0" 
                                                           placeholder="Search product by name, description, SKU, HSN..."
                                                           x-model="item.searchQuery"
                                                           @input="onProductSearchInput(index)"
                                                           @keydown.down.prevent="onSearchKeyDown($event, index)"
                                                           @keydown.up.prevent="onSearchKeyDown($event, index)"
                                                           @keydown.enter.prevent="onSearchKeyDown($event, index)"
                                                           @keydown.escape.prevent="closeProductSearch(index)"
                                                           autocomplete="off">
                                                    <button type="button" 
                                                            class="btn btn-light border border-start-0 text-muted" 
                                                            x-show="item.searchQuery" 
                                                            @click="item.searchQuery = ''; onProductSearchInput(index)">
                                                        <i class="fa-solid fa-xmark"></i>
                                                    </button>
                                                </div>
                                            </div>

                                            <!-- Table Header for Dropdown Results -->
                                            <div class="product-combobox-grid-header">
                                                <span>Product</span>
                                                <span class="text-center">Stock</span>
                                                <span class="text-end">Rate (GST)</span>
                                            </div>

                                            <!-- Results Container -->
                                            <div class="product-combobox-list" :id="`product-dropdown-list-${index}`">
                                                <!-- Loading state -->
                                                <template x-if="item.loading && (!item.results || item.results.length === 0)">
                                                    <div class="text-center py-3 text-muted small">
                                                        <i class="fa-solid fa-circle-notch fa-spin text-primary me-2"></i>Searching inventory...
                                                    </div>
                                                </template>

                                                <!-- Results items -->
                                                <template x-for="(prod, pIdx) in item.results" :key="prod.id">
                                                    <div class="product-combobox-row"
                                                         :class="{'is-selected': item.highlightIndex === pIdx}"
                                                         :title="prod.description ? (prod.name + ' — ' + prod.description) : prod.name"
                                                         @mouseenter="item.highlightIndex = pIdx"
                                                         @click="selectProduct(index, prod)">
                                                        <!-- Col 1: Product Name & Kit -->
                                                        <div class="pe-2 overflow-hidden d-flex align-items-center gap-1.5">
                                                            <span class="fw-semibold text-truncate item-name text-dark" style="font-size: 0.8125rem;" x-text="prod.name"></span>
                                                            <template x-if="prod.has_components">
                                                                <span class="product-badge-kit flex-shrink-0" style="font-size: 0.62rem; padding: 0.05rem 0.35rem;">
                                                                    <i class="fa-solid fa-boxes-stacked me-1"></i>Kit
                                                                </span>
                                                            </template>
                                                            <template x-if="prod.description && item.searchQuery && !prod.name.toLowerCase().includes(item.searchQuery.toLowerCase().trim())">
                                                                <span class="text-muted fst-italic text-truncate flex-shrink-1" style="font-size: 0.68rem;" x-text="'· ' + prod.description"></span>
                                                            </template>
                                                        </div>

                                                        <!-- Col 2: Stock Status -->
                                                        <div class="text-center px-1">
                                                            <template x-if="prod.current_stock > 0">
                                                                <span class="product-stock-badge in-stock">
                                                                    <i class="fa-solid fa-circle text-success me-1" style="font-size: 5px;"></i>
                                                                    <span x-text="prod.current_stock + ' ' + prod.unit_symbol"></span>
                                                                </span>
                                                            </template>
                                                            <template x-if="prod.current_stock <= 0">
                                                                <span class="product-stock-badge out-stock">
                                                                    <i class="fa-solid fa-circle text-danger me-1" style="font-size: 5px;"></i>
                                                                    <span>0 Stock</span>
                                                                </span>
                                                            </template>
                                                        </div>

                                                        <!-- Col 3: Selling Price & GST -->
                                                        <div class="text-end ps-1 text-nowrap">
                                                            <span class="fw-bold text-dark num-align" style="font-size: 0.8125rem;" x-text="'₹ ' + formatNumber(prod.price)"></span>
                                                            <span class="text-muted ms-1" style="font-size: 0.72rem; font-weight: 500;" x-text="'(' + (prod.tax_rate || 0) + '%)'"></span>
                                                        </div>
                                                    </div>
                                                </template>

                                                <!-- No results found -->
                                                <template x-if="!item.loading && item.results && item.results.length === 0">
                                                    <div class="p-3 text-center text-muted small">
                                                        <i class="fa-solid fa-box-open mb-1.5 d-block opacity-40 fs-4"></i>
                                                        <span>No products matching "<span class="fw-semibold text-dark" x-text="item.searchQuery"></span>"</span>
                                                    </div>
                                                </template>
                                            </div>

                                            <!-- Dropdown Footer -->
                                            <div class="product-combobox-footer d-flex justify-content-between align-items-center">
                                                <button type="button" class="btn btn-sm btn-link text-decoration-none p-0 fw-semibold text-primary d-flex align-items-center" @click="selectCustomItem(index)">
                                                    <i class="fa-solid fa-plus-circle me-1.5 fs-6"></i>
                                                    <span x-text="item.searchQuery ? `Use '${item.searchQuery}'` : 'Custom item'"></span>
                                                </button>
                                                <div class="d-flex align-items-center gap-1 text-muted" style="font-size: 0.68rem;">
                                                    <span class="badge bg-white text-secondary border">↑↓ Navigate</span>
                                                    <span class="badge bg-white text-secondary border">↵ Select</span>
                                                    <span class="badge bg-white text-secondary border">Esc Close</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <input type="text" :name="`items[${index}][description]`" class="form-control form-control-sm mt-1" x-model="item.description" :placeholder="item.product_id ? 'Description / Details (optional)...' : 'Item description...'" :required="!item.product_id">
                                    <template x-if="item.components && item.components.length > 0">
                                        <div class="mt-1 p-2 bg-light rounded border border-info-subtle small" style="font-size: 0.72rem;">
                                            <span class="text-primary fw-semibold"><i class="fa-solid fa-boxes-stacked me-1"></i> Auto-Deducted Components:</span>
                                            <div class="d-flex flex-wrap gap-1 mt-1">
                                                <template x-for="c in item.components">
                                                    <span class="badge bg-white text-dark border">
                                                        <span x-text="(c.qty * (parseFloat(item.quantity) || 1)).toFixed(1) + 'x ' + c.name"></span>
                                                        <span class="text-muted" x-text="'(Avail: ' + c.stock + ' ' + c.unit + ')'"></span>
                                                    </span>
                                                </template>
                                            </div>
                                        </div>
                                    </template>
                                </td>
                                <td>
                                    <input type="text" :name="`items[${index}][hsn_code]`" class="form-control form-control-sm" x-model="item.hsn_code">
                                </td>
                                <td>
                                    <input type="number" step="0.01" min="0.01" :name="`items[${index}][quantity]`" class="form-control form-control-sm text-end" x-model="item.quantity" @input="recalcRow(index)" required>
                                </td>
                                <td>
                                    <input type="number" 
                                           step="0.01" 
                                           min="0" 
                                           class="form-control form-control-sm text-end" 
                                           x-model="item.rate_inclusive" 
                                           @input="onRateInclusiveChange(index)" 
                                           placeholder="0.00">
                                </td>
                                <td>
                                    <input type="number" 
                                           step="0.01" 
                                           min="0" 
                                           :name="`items[${index}][unit_price]`" 
                                           class="form-control form-control-sm text-end" 
                                           x-model="item.unit_price" 
                                           @input="onUnitPriceChange(index)" 
                                           required>
                                </td>
                                <td>
                                    <input type="number" step="0.01" min="0" :name="`items[${index}][discount_amount]`" class="form-control form-control-sm text-end" x-model="item.discount_amount" @input="recalcRow(index)">
                                </td>
                                <td>
                                    <select :name="`items[${index}][gst_rate]`" class="form-select form-select-sm" x-model="item.gst_rate" @change="onGstRateChange(index)">
                                        <option value="0">0%</option>
                                        <option value="5">5%</option>
                                        <option value="12">12%</option>
                                        <option value="18">18%</option>
                                        <option value="28">28%</option>
                                    </select>
                                </td>
                                <td class="text-end small num-align fw-semibold text-muted" x-text="'₹ ' + formatNumber(item.tax_amount)"></td>
                                <td class="text-end fw-bold num-align" x-text="'₹ ' + formatNumber(item.total_amount)"></td>
                                <td>
                                    <select :name="`items[${index}][warehouse_id]`" class="form-select form-select-sm" x-model="item.warehouse_id">
                                        <option value="">-- Main Godown --</option>
                                        @foreach($warehouses as $w)
                                            <option value="{{ $w->id }}">{{ $w->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
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
        defaultWarehouseId: '{{ $warehouses->firstWhere('is_default', true)?->id ?? $warehouses->first()?->id ?? '' }}',
        searchDebounce: null,
        defaultProducts: [],
        items: [
            {
                product_id: '',
                selectedLabel: '',
                description: '',
                hsn_code: '',
                warehouse_id: '{{ $warehouses->firstWhere('is_default', true)?->id ?? $warehouses->first()?->id ?? '' }}',
                quantity: 1,
                rate_inclusive: 0,
                unit_price: 0,
                discount_amount: 0,
                gst_rate: 18,
                tax_amount: 0,
                total_amount: 0,
                components: [],
                isOpen: false,
                searchQuery: '',
                results: [],
                loading: false,
                highlightIndex: 0
            }
        ],
        init() {
            this.fetchDefaultProducts();
        },
        fetchDefaultProducts() {
            fetch('{{ route('api.products.search') }}?type=sales&limit=25')
                .then(res => res.json())
                .then(data => {
                    this.defaultProducts = data || [];
                    if (this.items[0] && (!this.items[0].results || this.items[0].results.length === 0)) {
                        this.items[0].results = [...this.defaultProducts];
                    }
                })
                .catch(err => console.error('Failed to load initial products:', err));
        },
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
            return (parseFloat(num) || 0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        },
        addItem() {
            this.items.push({
                product_id: '',
                selectedLabel: '',
                description: '',
                hsn_code: '',
                warehouse_id: this.defaultWarehouseId,
                quantity: 1,
                rate_inclusive: 0,
                unit_price: 0,
                discount_amount: 0,
                gst_rate: 18,
                tax_amount: 0,
                total_amount: 0,
                components: [],
                isOpen: false,
                searchQuery: '',
                results: [...this.defaultProducts],
                loading: false,
                highlightIndex: 0
            });
        },
        removeItem(index) {
            if (this.items.length > 1) {
                this.items.splice(index, 1);
            }
        },
        openProductSearch(index) {
            // Close other rows' search menus
            this.items.forEach((it, i) => {
                if (i !== index) it.isOpen = false;
            });

            const item = this.items[index];
            item.isOpen = true;
            item.highlightIndex = 0;

            if ((!item.results || item.results.length === 0) && (!item.searchQuery || item.searchQuery === '')) {
                if (this.defaultProducts.length > 0) {
                    item.results = [...this.defaultProducts];
                } else {
                    this.fetchProducts(index, '');
                }
            }

            this.$nextTick(() => {
                const input = document.getElementById(`product-search-input-${index}`);
                if (input) {
                    input.focus();
                    input.select();
                }
            });
        },
        closeProductSearch(index) {
            if (this.items[index]) {
                this.items[index].isOpen = false;
            }
        },
        onProductSearchInput(index) {
            clearTimeout(this.searchDebounce);
            const item = this.items[index];
            const q = (item.searchQuery || '').trim();

            if (q === '') {
                item.results = [...this.defaultProducts];
                item.loading = false;
                item.highlightIndex = 0;
                return;
            }

            item.loading = true;
            this.searchDebounce = setTimeout(() => {
                this.fetchProducts(index, q);
            }, 200);
        },
        fetchProducts(index, query) {
            const item = this.items[index];
            if (!item) return;

            item.loading = true;
            fetch(`{{ route('api.products.search') }}?type=sales&q=${encodeURIComponent(query || '')}`)
                .then(res => res.json())
                .then(data => {
                    item.results = data || [];
                    item.loading = false;
                    item.highlightIndex = 0;
                })
                .catch(err => {
                    console.error('Product search error:', err);
                    item.results = [];
                    item.loading = false;
                });
        },
        selectProduct(index, prod) {
            const item = this.items[index];
            if (!item || !prod) return;

            item.product_id = prod.id;
            item.selectedLabel = prod.name;
            item.selectedStock = `${prod.current_stock} ${prod.unit_symbol}`;
            item.description = '';
            item.hsn_code = prod.hsn || '';
            item.unit_price = parseFloat(prod.price) || 0;
            item.gst_rate = parseFloat(prod.tax_rate) || 0;
            const gst = item.gst_rate;
            const incl = gst > 0 ? (item.unit_price * (1 + (gst / 100))) : item.unit_price;
            item.rate_inclusive = Math.round(incl * 100) / 100;
            item.components = prod.components || [];

            this.recalcRow(index);
            item.isOpen = false;
        },
        clearProduct(index) {
            const item = this.items[index];
            if (!item) return;

            item.product_id = '';
            item.selectedLabel = '';
            item.selectedStock = '';
            item.description = '';
            item.components = [];
            item.searchQuery = '';
            item.unit_price = 0;
            item.rate_inclusive = 0;
            item.results = [...this.defaultProducts];
            item.isOpen = false;
            this.recalcRow(index);
        },
        selectCustomItem(index) {
            const item = this.items[index];
            if (!item) return;

            item.product_id = '';
            const q = (item.searchQuery || '').trim();
            if (q.length > 0) {
                item.description = q;
                item.selectedLabel = q;
            } else {
                item.selectedLabel = 'Custom Item';
            }
            item.selectedStock = '';
            item.components = [];
            item.isOpen = false;
        },
        onSearchKeyDown(event, index) {
            const item = this.items[index];
            if (!item) return;

            if (event.key === 'ArrowDown') {
                if (item.results && item.results.length > 0) {
                    item.highlightIndex = (item.highlightIndex + 1) % item.results.length;
                    this.scrollHighlightedIntoView(index);
                }
            } else if (event.key === 'ArrowUp') {
                if (item.results && item.results.length > 0) {
                    item.highlightIndex = (item.highlightIndex - 1 + item.results.length) % item.results.length;
                    this.scrollHighlightedIntoView(index);
                }
            } else if (event.key === 'Enter') {
                if (item.results && item.results.length > 0 && item.results[item.highlightIndex]) {
                    this.selectProduct(index, item.results[item.highlightIndex]);
                } else {
                    this.selectCustomItem(index);
                }
            } else if (event.key === 'Escape') {
                this.closeProductSearch(index);
            }
        },
        scrollHighlightedIntoView(index) {
            this.$nextTick(() => {
                const list = document.getElementById(`product-dropdown-list-${index}`);
                if (!list) return;
                const items = list.querySelectorAll('.product-combobox-row');
                const target = items[this.items[index].highlightIndex];
                if (target) {
                    target.scrollIntoView({ block: 'nearest' });
                }
            });
        },
        onRateInclusiveChange(index) {
            const item = this.items[index];
            if (!item) return;

            const rateIncl = parseFloat(item.rate_inclusive);
            if (isNaN(rateIncl) || rateIncl === 0) {
                item.unit_price = 0;
                this.recalcRow(index);
                return;
            }

            const gst = parseFloat(item.gst_rate) || 0;
            const excl = gst > 0 ? (rateIncl / (1 + (gst / 100))) : rateIncl;
            item.unit_price = Math.round(excl * 100) / 100;
            this.recalcRow(index);
        },
        onUnitPriceChange(index) {
            const item = this.items[index];
            if (!item) return;

            const price = parseFloat(item.unit_price);
            if (isNaN(price) || price === 0) {
                item.rate_inclusive = 0;
                this.recalcRow(index);
                return;
            }

            const gst = parseFloat(item.gst_rate) || 0;
            const incl = gst > 0 ? (price * (1 + (gst / 100))) : price;
            item.rate_inclusive = Math.round(incl * 100) / 100;
            this.recalcRow(index);
        },
        onGstRateChange(index) {
            const item = this.items[index];
            if (!item) return;

            const price = parseFloat(item.unit_price) || 0;
            const gst = parseFloat(item.gst_rate) || 0;
            if (price > 0) {
                const incl = gst > 0 ? (price * (1 + (gst / 100))) : price;
                item.rate_inclusive = Math.round(incl * 100) / 100;
            } else if (parseFloat(item.rate_inclusive) > 0) {
                const incl = parseFloat(item.rate_inclusive);
                const excl = gst > 0 ? (incl / (1 + (gst / 100))) : incl;
                item.unit_price = Math.round(excl * 100) / 100;
            }
            this.recalcRow(index);
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
