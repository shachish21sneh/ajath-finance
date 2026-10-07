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

            <div class="table-responsive table-responsive-combobox" style="min-height: 280px;">
                <table class="table table-bordered align-middle">
                    <thead class="table-light small text-uppercase">
                        <tr>
                            <th style="min-width: 280px;">Item / Product *</th>
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
                                <td class="position-relative">
                                    <!-- Hidden input for product_id -->
                                    <input type="hidden" :name="`items[${index}][product_id]`" :value="item.product_id">

                                    <!-- Product Combobox Input Trigger -->
                                    <div class="product-combobox-wrapper">
                                        <button type="button" 
                                                class="product-combobox-trigger"
                                                :class="{'is-active': item.isOpen, 'border-primary': item.product_id}"
                                                @click="openProductSearch(index)"
                                                :title="item.selectedLabel || '-- Choose Product / Item --'">
                                            <div class="d-flex align-items-center text-truncate me-2" style="min-width: 0; flex: 1 1 auto;">
                                                <span class="text-truncate" :class="item.selectedLabel ? 'fw-semibold text-dark' : 'text-muted'" x-text="item.selectedLabel || '-- Choose Product / Item --'"></span>
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
                                                            <template x-if="prod.description && item.searchQuery && prod.description.toLowerCase().includes(item.searchQuery.toLowerCase().trim()) && !prod.name.toLowerCase().includes(item.searchQuery.toLowerCase().trim())">
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
        searchDebounce: null,
        defaultProducts: [],
        items: [
            {
                product_id: '',
                selectedLabel: '',
                description: '',
                hsn_code: '',
                quantity: 1,
                unit_price: 0,
                discount_amount: 0,
                gst_rate: 18,
                tax_amount: 0,
                total_amount: 0,
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
                unit_price: 0,
                discount_amount: 0,
                gst_rate: 18,
                tax_amount: 0,
                total_amount: 0,
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
                    item.loading = false;
                    item.highlightIndex = 0;
                })
                .catch(err => {
                    console.error('Purchase product search error:', err);
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
            item.searchQuery = '';
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
                const list = document.getElementById(`purchase-dropdown-list-${index}`);
                if (!list) return;
                const items = list.querySelectorAll('.product-combobox-row');
                const target = items[this.items[index].highlightIndex];
                if (target) {
                    target.scrollIntoView({ block: 'nearest' });
                }
            });
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
