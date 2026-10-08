@extends('layouts.app')

@section('title', 'Edit Purchase Bill ' . $purchase->bill_no)

@section('content')
<div x-data="purchaseForm()" class="pb-5">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="fa-solid fa-pen-to-square text-warning me-2"></i> Edit Vendor Purchase Bill: {{ $purchase->bill_no }}</h4>
            <p class="text-muted small mb-0">Modify supplier bill details, reconcile inward stock, and update balanced Purchase Voucher</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('purchases.show', $purchase->id) }}" class="btn btn-sm btn-outline-secondary">
                <i class="fa-solid fa-arrow-left me-1"></i> Back to Bill
            </a>
            <button type="button" class="btn btn-primary btn-sm fw-semibold px-4" @click="submitForm()" :disabled="isSubmitting">
                <span x-show="!isSubmitting"><i class="fa-solid fa-check me-1"></i> Save Changes (Ctrl+S)</span>
                <span x-show="isSubmitting"><i class="fa-solid fa-spinner fa-spin me-1"></i> Saving...</span>
            </button>
        </div>
    </div>

    <form id="purchaseForm" class="keyboard-save-form" action="{{ route('purchases.update', $purchase->id) }}" method="POST" @submit="handleSubmit($event)" @keydown.enter="handleEnterKey($event)">
        @csrf
        @method('PUT')
        <!-- Supplier & Bill Meta -->
        <div class="card card-modern p-4 mb-4">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Supplier / Vendor *</label>
                    <select name="supplier_ledger_id" class="form-select" required>
                        <option value="">-- Choose Supplier --</option>
                        @foreach($suppliers as $s)
                            <option value="{{ $s->id }}" {{ (string)old('supplier_ledger_id', $purchase->supplier_ledger_id) === (string)$s->id ? 'selected' : '' }}>
                                {{ $s->name }} ({{ $s->state }}) [GST: {{ $s->gstin ?: 'None' }}]
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Vendor Bill # *</label>
                    <input type="text" name="bill_no" class="form-control fw-bold" value="{{ old('bill_no', $purchase->bill_no) }}" required placeholder="e.g. INV-9901">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Bill Date *</label>
                    <input type="date" name="bill_date" class="form-control" value="{{ old('bill_date', $purchase->bill_date->format('Y-m-d')) }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Payment Due Date</label>
                    <input type="date" name="due_date" class="form-control" value="{{ old('due_date', $purchase->due_date ? $purchase->due_date->format('Y-m-d') : '') }}">
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

            <div class="table-responsive table-responsive-combobox" style="min-height: 280px;">
                <table class="table table-bordered align-middle mb-0 invoice-items-table w-100">
                    <thead class="table-light small text-uppercase">
                        <tr>
                            <th style="min-width: 180px;">Item / Product *</th>
                            <th style="width: 85px;">HSN Code</th>
                            <th style="width: 95px;" class="text-end">Qty *</th>
                            <th style="width: 110px;" class="text-end">Rate (Incl. of Tax)</th>
                            <th style="width: 95px;" class="text-end">Unit Rate (₹) *</th>
                            <th style="width: 75px;" class="text-end">Disc (₹)</th>
                            <th style="width: 75px;">GST %</th>
                            <th style="width: 90px;" class="text-end text-nowrap">Tax (₹)</th>
                            <th style="width: 105px;" class="text-end text-nowrap">Total (₹)</th>
                            <th style="width: 130px;">Godown</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(item, index) in items" :key="index">
                            <tr>
                                <td class="position-relative">
                                    <!-- Hidden input for product_id -->
                                    <input type="hidden" :name="`items[${index}][product_id]`" :value="item.product_id">

                                    <!-- Product Combobox Input Trigger -->
                                    <div class="product-combobox-wrapper">
                                        <button type="button" 
                                                class="product-combobox-trigger"
                                                :class="{'is-active': item.isOpen, 'border-primary': item.product_id}"
                                                @click="openProductSearch(index)"
                                                :title="item.selectedLabel || '-- Choose Product --'">
                                            <div class="d-flex align-items-center text-truncate me-2" style="min-width: 0; flex: 1 1 auto;">
                                                <span class="text-truncate" :class="item.selectedLabel ? 'fw-semibold text-dark' : 'text-muted'" x-text="item.selectedLabel || '-- Choose Product --'"></span>
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
                                                           :id="`purchase-product-search-${index}`"
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
                                                <span>Item / Product</span>
                                                <span class="text-center">Stock</span>
                                                <span class="text-end">Rate (GST)</span>
                                            </div>

                                            <!-- Results Container -->
                                            <div class="product-combobox-list" :id="`purchase-dropdown-list-${index}`">
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
                                                        <!-- Col 1: Product Name -->
                                                        <div class="pe-2 overflow-hidden d-flex align-items-center gap-1.5">
                                                            <span class="fw-semibold text-truncate item-name text-dark" style="font-size: 0.8125rem;" x-text="prod.name"></span>
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

                                                        <!-- Col 3: Buying Price & GST -->
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
                                                        <span>No items matching "<span class="fw-semibold text-dark" x-text="item.searchQuery"></span>"</span>
                                                    </div>
                                                </template>
                                            </div>

                                            <!-- Dropdown Footer -->
                                            <div class="product-combobox-footer d-flex justify-content-end align-items-center">
                                                <div class="d-flex align-items-center gap-1 text-muted" style="font-size: 0.68rem;">
                                                    <span class="badge bg-white text-secondary border">↑↓ Navigate</span>
                                                    <span class="badge bg-white text-secondary border">↵ Select</span>
                                                    <span class="badge bg-white text-secondary border">Esc Close</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <textarea :name="`items[${index}][description]`" 
                                              class="form-control form-control-sm mt-1" 
                                              rows="2" 
                                              x-model="item.description" 
                                              :placeholder="item.product_id ? 'Description / Details / Serial Nos (optional)...' : 'Item description / details...'" 
                                              :required="!item.product_id"
                                              @keydown.enter.stop
                                              style="resize: vertical; min-height: 48px; font-size: 0.8125rem; line-height: 1.35;"></textarea>
                                </td>
                                <td>
                                    <input type="text" :name="`items[${index}][hsn_code]`" class="form-control form-control-sm text-center" x-model="item.hsn_code" placeholder="HSN">
                                </td>
                                <td>
                                    <div class="input-group input-group-sm">
                                        <input type="number" step="1" min="1" :name="`items[${index}][quantity]`" class="form-control form-control-sm text-end num-align px-1" x-model="item.quantity" @input="recalcRow(index)" required>
                                        <span class="input-group-text px-1 text-muted text-uppercase fw-semibold" style="font-size: 0.68rem; min-width: 30px; justify-content: center;" x-show="item.unit_symbol" x-text="item.unit_symbol"></span>
                                    </div>
                                </td>
                                <td>
                                    <input type="number" 
                                           step="0.01" 
                                           min="0" 
                                           class="form-control form-control-sm text-end num-align" 
                                           x-model="item.rate_inclusive" 
                                           @input="onRateInclusiveChange(index)" 
                                           placeholder="0.00">
                                </td>
                                <td>
                                    <input type="number" 
                                           step="0.01" 
                                           min="0" 
                                           :name="`items[${index}][unit_price]`" 
                                           class="form-control form-control-sm text-end num-align" 
                                           x-model="item.unit_price" 
                                           @input="onUnitPriceChange(index)" 
                                           required>
                                </td>
                                <td>
                                    <input type="number" step="0.01" min="0" :name="`items[${index}][discount_amount]`" class="form-control form-control-sm text-end num-align" x-model="item.discount_amount" @input="recalcRow(index)">
                                </td>
                                <td>
                                    <select :name="`items[${index}][gst_rate]`" class="form-select form-select-sm px-1" x-model="item.gst_rate" @change="onGstRateChange(index)">
                                        <option value="0">0%</option>
                                        <option value="5">5%</option>
                                        <option value="12">12%</option>
                                        <option value="18">18%</option>
                                        <option value="28">28%</option>
                                    </select>
                                </td>
                                <td class="text-end small num-align fw-semibold text-muted text-nowrap" x-text="'₹ ' + formatNumber(item.tax_amount)"></td>
                                <td class="text-end fw-bold num-align text-nowrap" x-text="'₹ ' + formatNumber(item.total_amount)"></td>
                                <td>
                                    <div class="d-flex align-items-center gap-1">
                                        <select :name="`items[${index}][warehouse_id]`" class="form-select form-select-sm px-1 flex-grow-1" x-model="item.warehouse_id">
                                            <option value="">-- Main Godown --</option>
                                            @foreach($warehouses as $w)
                                                <option value="{{ $w->id }}">{{ $w->name }}</option>
                                            @endforeach
                                        </select>
                                        <button type="button" 
                                                class="btn btn-sm text-danger p-0 ms-1 flex-shrink-0" 
                                                @click="removeItem(index)" 
                                                x-show="items.length > 1" 
                                                title="Delete item">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </div>
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

        <div class="card card-modern p-4 mb-4">
            <h6 class="fw-bold mb-3">Payment & Vendor Notes</h6>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Amount Paid Now (₹)</label>
                    <input type="number" step="0.01" name="paid_amount" class="form-control" value="{{ old('paid_amount', number_format($purchase->paid_amount, 2, '.', '')) }}">
                </div>
                <div class="col-md-8">
                    <label class="form-label small fw-semibold">Purchase Notes / Remarks</label>
                    <input type="text" name="notes" class="form-control" value="{{ old('notes', $purchase->notes) }}" placeholder="e.g. GRN verified, gate pass #123...">
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2">
            <a href="{{ route('purchases.show', $purchase->id) }}" class="btn btn-light border px-4">Cancel</a>
            <button type="submit" class="btn btn-primary px-5 fw-semibold py-2" :disabled="isSubmitting">
                <span x-show="!isSubmitting"><i class="fa-solid fa-check me-1"></i> Save Changes (Ctrl+S)</span>
                <span x-show="isSubmitting"><i class="fa-solid fa-spinner fa-spin me-1"></i> Saving Changes...</span>
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function purchaseForm() {
    return {
        isSubmitting: false,
        defaultWarehouseId: '{{ $warehouses->firstWhere('is_default', true)?->id ?? $warehouses->first()?->id ?? '' }}',
        searchDebounce: null,
        defaultProducts: [],
        items: {!! json_encode($purchase->items->map(function($item) use ($warehouses) {
            $prod = $item->product;
            $rateIncl = round((float)$item->unit_price * (1 + ((float)$item->gst_rate / 100)), 2);
            $taxAmt = round((float)$item->cgst_amount + (float)$item->sgst_amount + (float)$item->igst_amount, 2);
            return [
                'product_id' => $item->product_id ? (string)$item->product_id : '',
                'selectedLabel' => $prod ? ($prod->name . ($prod->sku ? ' (' . $prod->sku . ')' : '')) : ($item->description ?? ''),
                'description' => $item->description ?? '',
                'hsn_code' => $item->hsn_code ?? '',
                'quantity' => (float)$item->quantity,
                'rate_inclusive' => $rateIncl,
                'unit_price' => (float)$item->unit_price,
                'discount_amount' => (float)($item->discount_amount ?? 0),
                'gst_rate' => (float)$item->gst_rate,
                'tax_amount' => $taxAmt,
                'total_amount' => (float)$item->total_amount,
                'unit_symbol' => $prod?->unit?->symbol ?? '',
                'warehouse_id' => $item->warehouse_id ? (string)$item->warehouse_id : (string)($warehouses->firstWhere('is_default', true)?->id ?? $warehouses->first()?->id ?? ''),
                'isOpen' => false,
                'searchQuery' => '',
                'results' => [],
                'loading' => false,
                'highlightIndex' => 0
            ];
        })) !!},
        init() {
            if (!this.items || this.items.length === 0) {
                this.addItem();
            }
            this.fetchDefaultProducts();
        },
        fetchDefaultProducts() {
            fetch('{{ route('api.products.search') }}?type=purchase&limit=25')
                .then(res => res.json())
                .then(data => {
                    this.defaultProducts = data || [];
                    if (this.items[0] && (!this.items[0].results || this.items[0].results.length === 0)) {
                        this.items[0].results = [...this.defaultProducts];
                    }
                })
                .catch(err => console.error('Failed to load initial purchase items:', err));
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
        get grandTotal() {
            return Math.round(this.taxableAmount + this.totalTax);
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
                quantity: 1,
                rate_inclusive: 0,
                unit_price: 0,
                discount_amount: 0,
                gst_rate: 18,
                tax_amount: 0,
                total_amount: 0,
                unit_symbol: '',
                warehouse_id: this.defaultWarehouseId,
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
                const input = document.getElementById(`purchase-product-search-${index}`);
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
            fetch(`{{ route('api.products.search') }}?type=purchase&q=${encodeURIComponent(query || '')}`)
                .then(res => res.json())
                .then(data => {
                    item.results = data || [];
                    item.highlightIndex = 0;
                })
                .catch(err => {
                    console.error('Failed to search purchase products:', err);
                    item.results = [];
                })
                .finally(() => {
                    item.loading = false;
                });
        },
        selectProduct(index, prod) {
            const item = this.items[index];
            item.product_id = String(prod.id);
            item.selectedLabel = prod.name + (prod.sku ? ` (${prod.sku})` : '');
            item.unit_price = parseFloat(prod.price) || 0;
            item.gst_rate = parseFloat(prod.tax_rate) || 0;
            item.hsn_code = prod.hsn_code || '';
            item.description = prod.description || '';
            item.unit_symbol = prod.unit_symbol || '';
            
            const rate = parseFloat(item.unit_price) || 0;
            const gst = parseFloat(item.gst_rate) || 0;
            item.rate_inclusive = Math.round((rate * (1 + (gst / 100))) * 100) / 100;

            item.isOpen = false;
            item.searchQuery = '';
            this.recalcRow(index);
        },
        clearProduct(index) {
            const item = this.items[index];
            item.product_id = '';
            item.selectedLabel = '';
            item.description = '';
            item.hsn_code = '';
            item.unit_symbol = '';
            item.results = [...this.defaultProducts];
            this.recalcRow(index);
        },
        onSearchKeyDown(e, index) {
            const item = this.items[index];
            if (!item || !item.isOpen) return;

            const total = item.results.length;
            if (e.key === 'ArrowDown') {
                if (total > 0) {
                    item.highlightIndex = (item.highlightIndex + 1) % total;
                    this.scrollHighlightedIntoView(index);
                }
            } else if (e.key === 'ArrowUp') {
                if (total > 0) {
                    item.highlightIndex = (item.highlightIndex - 1 + total) % total;
                    this.scrollHighlightedIntoView(index);
                }
            } else if (e.key === 'Enter') {
                if (total > 0 && item.results[item.highlightIndex]) {
                    this.selectProduct(index, item.results[item.highlightIndex]);
                }
            }
        },
        scrollHighlightedIntoView(index) {
            this.$nextTick(() => {
                const list = document.getElementById(`purchase-dropdown-list-${index}`);
                if (!list) return;
                const active = list.querySelector('.product-combobox-row.is-selected');
                if (active) {
                    active.scrollIntoView({ block: 'nearest' });
                }
            });
        },
        onRateInclusiveChange(index) {
            const item = this.items[index];
            const inc = parseFloat(item.rate_inclusive) || 0;
            const gst = parseFloat(item.gst_rate) || 0;
            if (inc > 0) {
                item.unit_price = Math.round((inc / (1 + (gst / 100))) * 100) / 100;
            } else {
                item.unit_price = 0;
            }
            this.recalcRow(index);
        },
        onUnitPriceChange(index) {
            const item = this.items[index];
            const rate = parseFloat(item.unit_price) || 0;
            const gst = parseFloat(item.gst_rate) || 0;
            if (rate > 0) {
                item.rate_inclusive = Math.round((rate * (1 + (gst / 100))) * 100) / 100;
            } else {
                item.rate_inclusive = 0;
            }
            this.recalcRow(index);
        },
        onGstRateChange(index) {
            const item = this.items[index];
            const inc = parseFloat(item.rate_inclusive) || 0;
            const rate = parseFloat(item.unit_price) || 0;
            if (inc > 0) {
                this.onRateInclusiveChange(index);
            } else if (rate > 0) {
                this.onUnitPriceChange(index);
            } else {
                this.recalcRow(index);
            }
        },
        recalcRow(index) {
            const item = this.items[index];
            const qty = parseFloat(item.quantity) || 0;
            const price = parseFloat(item.unit_price) || 0;
            const disc = parseFloat(item.discount_amount) || 0;
            const gstRate = parseFloat(item.gst_rate) || 0;

            const taxable = Math.max(0, (qty * price) - disc);
            const tax = taxable * (gstRate / 100);

            item.tax_amount = tax;
            item.total_amount = taxable + tax;
        },
        handleEnterKey(event) {
            if (event.target.tagName === 'TEXTAREA') {
                return;
            }
            if (event.target.closest('.dropdown-menu')) {
                return;
            }
            event.preventDefault();
        },
        handleSubmit(event) {
            if (this.isSubmitting) {
                event.preventDefault();
                return false;
            }
            this.isSubmitting = true;
            return true;
        },
        submitForm() {
            if (this.isSubmitting) return;
            this.isSubmitting = true;
            document.getElementById('purchaseForm').submit();
        }
    };
}
</script>
@endpush
