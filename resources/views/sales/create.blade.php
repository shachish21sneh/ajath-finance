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
                <div class="col-md-4 position-relative">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <label class="form-label small fw-semibold mb-0">Customer / Client *</label>
                        <div class="d-flex align-items-center gap-1.5">
                            <button type="button" 
                                    class="btn btn-sm py-0 px-2 rounded d-inline-flex align-items-center justify-content-center"
                                    :class="hasCustomShipTo ? 'btn-primary text-white shadow-sm' : 'btn-light border text-primary'" 
                                    style="height: 26px; min-width: 28px;"
                                    @click="openShipToModal()" 
                                    :title="hasCustomShipTo ? 'Edit Ship To Address' : 'Add Ship To Address'">
                                <i class="fa-solid fa-truck-fast" style="font-size: 0.75rem;"></i>
                            </button>
                            <button type="button" 
                                    class="btn btn-sm btn-light border py-0 px-2 text-primary rounded d-inline-flex align-items-center justify-content-center" 
                                    style="height: 26px; min-width: 28px;"
                                    @click="openCustomerModal()" 
                                    title="Add New Customer">
                                <i class="fa-solid fa-user-plus" style="font-size: 0.75rem;"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Hidden input for form submit -->
                    <input type="hidden" name="customer_ledger_id" :value="customerId">

                    <!-- Customer Combobox Input Trigger -->
                    <div class="product-combobox-wrapper">
                        <button type="button" 
                                id="customer-combobox-trigger"
                                class="product-combobox-trigger"
                                :class="{'is-active': isCustomerOpen, 'border-primary': customerId}"
                                style="height: 38px; min-height: 38px; font-size: 0.875rem;"
                                @click="toggleCustomerDropdown()"
                                :title="selectedCustomerLabel || '-- Choose Customer --'">
                            <div class="d-flex align-items-center text-truncate me-2" style="min-width: 0; flex: 1 1 auto;">
                                <span class="text-truncate" :class="customerId ? 'fw-semibold text-dark' : 'text-muted'" x-text="selectedCustomerLabel || '-- Choose Customer --'"></span>
                            </div>
                            <div class="d-flex align-items-center flex-shrink-0 ms-auto gap-1">
                                <span x-show="customerId" 
                                      @click.stop="clearCustomer()" 
                                      class="product-clear-btn" 
                                      title="Clear customer selection">
                                    <i class="fa-solid fa-xmark"></i>
                                </span>
                                <i class="fa-solid fa-chevron-down text-muted small opacity-75"></i>
                            </div>
                        </button>

                        <!-- Floating Customer Search Dropdown Menu -->
                        <div x-show="isCustomerOpen" 
                             @click.outside="closeCustomerDropdown()"
                             class="product-combobox-menu w-100"
                             style="min-width: 380px;"
                             x-cloak>
                            
                            <!-- Live Search Bar -->
                            <div class="product-combobox-search-box">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-white border-end-0 text-muted ps-2.5">
                                        <i class="fa-solid fa-magnifying-glass text-primary"></i>
                                    </span>
                                    <input type="text" 
                                           id="customer-search-input"
                                           class="form-control product-combobox-search-input border-start-0" 
                                           placeholder="Search customer by name, GSTIN, phone, state..."
                                           x-model="customerSearchQuery"
                                           @input="onCustomerSearchInput()"
                                           @keydown.down.prevent="onCustomerKeyDown($event)"
                                           @keydown.up.prevent="onCustomerKeyDown($event)"
                                           @keydown.enter.prevent="onCustomerKeyDown($event)"
                                           @keydown.escape.prevent="closeCustomerDropdown()"
                                           autocomplete="off">
                                    <button type="button" 
                                            class="btn btn-light border border-start-0 text-muted" 
                                            x-show="customerSearchQuery" 
                                            @click="customerSearchQuery = ''; onCustomerSearchInput()">
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Table Header for Dropdown Results -->
                            <div class="product-combobox-grid-header" style="grid-template-columns: 1fr auto;">
                                <span>Customer / Client</span>
                                <span class="text-end">State / GSTIN</span>
                            </div>

                            <!-- Results Container -->
                            <div class="product-combobox-list" id="customer-dropdown-list" style="max-height: 250px;">
                                <template x-for="(cust, cIdx) in filteredCustomers" :key="cust.id">
                                    <div class="product-combobox-row d-flex justify-content-between align-items-center py-2 px-2.5 cursor-pointer"
                                         :class="{'is-selected': customerHighlightIndex === cIdx}"
                                         @mouseenter="customerHighlightIndex = cIdx"
                                         @click="selectCustomer(cust)">
                                        <div class="pe-2 overflow-hidden">
                                            <div class="fw-semibold text-truncate text-dark item-name" style="font-size: 0.8125rem;" x-text="cust.name"></div>
                                            <div class="text-muted small d-flex align-items-center gap-1.5" style="font-size: 0.7rem;">
                                                <template x-if="cust.phone">
                                                    <span><i class="fa-solid fa-phone me-0.5"></i><span x-text="cust.phone"></span></span>
                                                </template>
                                                <template x-if="cust.city">
                                                    <span>• <span x-text="cust.city"></span></span>
                                                </template>
                                            </div>
                                        </div>
                                        <div class="text-end ps-1 flex-shrink-0">
                                            <span class="badge bg-light text-dark border" style="font-size: 0.68rem;" x-text="cust.state || cust.state_code"></span>
                                            <div class="text-muted" style="font-size: 0.68rem;" x-text="cust.gstin ? cust.gstin : 'Unregistered'"></div>
                                        </div>
                                    </div>
                                </template>

                                <!-- No results found -->
                                <template x-if="filteredCustomers.length === 0">
                                    <div class="p-3 text-center text-muted small">
                                        <i class="fa-solid fa-user-slash mb-1.5 d-block opacity-40 fs-4"></i>
                                        <span>No customer matching "<span class="fw-semibold text-dark" x-text="customerSearchQuery"></span>"</span>
                                        <div class="mt-2.5">
                                            <button type="button" class="btn btn-sm btn-primary" @click="openCustomerModal(customerSearchQuery)">
                                                <i class="fa-solid fa-plus me-1"></i> Add "<span x-text="customerSearchQuery"></span>" as New Customer
                                            </button>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <!-- Dropdown Footer -->
                            <div class="product-combobox-footer d-flex justify-content-between align-items-center">
                                <button type="button" class="btn btn-sm btn-link text-decoration-none p-0 fw-semibold text-primary d-flex align-items-center" @click="openCustomerModal(customerSearchQuery)">
                                    <i class="fa-solid fa-plus-circle me-1.5 fs-6"></i>
                                    <span>+ Add New Customer</span>
                                </button>
                                <div class="d-flex align-items-center gap-1 text-muted" style="font-size: 0.68rem;">
                                    <span class="badge bg-white text-secondary border">↑↓ Navigate</span>
                                    <span class="badge bg-white text-secondary border">↵ Select</span>
                                    <span class="badge bg-white text-secondary border">Esc Close</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Shipping Address Indicator / Quick Action -->
                    <div class="mt-1 d-flex align-items-center justify-content-between" style="font-size: 0.72rem;">
                        <div class="text-truncate me-1">
                            <template x-if="hasCustomShipTo">
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle fw-medium text-truncate d-inline-flex align-items-center" :title="shipTo.name + (shipTo.city ? ' - ' + shipTo.city : '')">
                                    <i class="fa-solid fa-truck-ramp-box me-1"></i>
                                    <span>Ship to: <strong x-text="shipTo.name + (shipTo.city ? ' (' + shipTo.city + ')' : '')"></strong></span>
                                </span>
                            </template>
                            <template x-if="!hasCustomShipTo">
                                <span class="text-muted">
                                    <i class="fa-solid fa-location-dot me-1 opacity-75"></i> Ship to: Same as billing address
                                </span>
                            </template>
                        </div>
                        <div class="flex-shrink-0">
                            <template x-if="hasCustomShipTo">
                                <span class="d-inline-flex gap-1.5 align-items-center">
                                    <a href="javascript:void(0)" class="text-primary text-decoration-none fw-semibold" @click="openShipToModal()">Edit</a>
                                    <span class="text-muted">|</span>
                                    <a href="javascript:void(0)" class="text-danger text-decoration-none fw-semibold" @click="resetShipTo()" title="Reset to billing address">Reset</a>
                                </span>
                            </template>
                            <template x-if="!hasCustomShipTo">
                                <a href="javascript:void(0)" class="text-primary text-decoration-none fw-semibold" @click="openShipToModal()">
                                    + Ship To
                                </a>
                            </template>
                        </div>
                    </div>

                    <!-- Hidden inputs for Shipping Address (saved only with this bill, not master) -->
                    <input type="hidden" name="shipping_name" :value="hasCustomShipTo ? shipTo.name : ''">
                    <input type="hidden" name="shipping_phone" :value="hasCustomShipTo ? shipTo.phone : ''">
                    <input type="hidden" name="shipping_email" :value="hasCustomShipTo ? shipTo.email : ''">
                    <input type="hidden" name="shipping_gstin" :value="hasCustomShipTo ? shipTo.gstin : ''">
                    <input type="hidden" name="shipping_address" :value="hasCustomShipTo ? shipTo.address : ''">
                    <input type="hidden" name="shipping_city" :value="hasCustomShipTo ? shipTo.city : ''">
                    <input type="hidden" name="shipping_state" :value="hasCustomShipTo ? shipTo.state : ''">
                    <input type="hidden" name="shipping_state_code" :value="hasCustomShipTo ? shipTo.state_code : ''">
                    <input type="hidden" name="shipping_pincode" :value="hasCustomShipTo ? shipTo.pincode : ''">
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
                <table class="table table-bordered align-middle mb-0 invoice-items-table w-100">
                    <thead class="table-light small text-uppercase">
                        <tr>
                            <th style="min-width: 180px;">Item / Product *</th>
                            <th style="width: 85px;">HSN/SAC</th>
                            <th style="width: 95px;" class="text-end">Qty *</th>
                            <th style="width: 110px;" class="text-end">Rate (Incl. of Tax)</th>
                            <th style="width: 95px;" class="text-end">Rate (₹) *</th>
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
                                    <!-- Hidden input for product_id form submit -->
                                    <input type="hidden" :name="`items[${index}][product_id]`" :value="item.product_id">

                                    <!-- Product Combobox Input Trigger -->
                                    <div class="product-combobox-wrapper">
                                        <button type="button" 
                                                class="product-combobox-trigger"
                                                :class="{'is-active': item.isOpen, 'border-primary': item.product_id}"
                                                @click="openProductSearch(index)"
                                                :title="item.selectedLabel || '-- Select Product --'">
                                            <div class="d-flex align-items-center text-truncate me-2" style="min-width: 0; flex: 1 1 auto;">
                                                <span class="text-truncate" :class="item.selectedLabel ? 'fw-semibold text-dark' : 'text-muted'" x-text="item.selectedLabel || '-- Select Product --'"></span>
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
                                            <div class="product-combobox-footer d-flex justify-content-end align-items-center">
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

    <!-- Quick Add Customer Modal -->
    <div class="modal fade" id="quickAddCustomerModal" tabindex="-1" aria-labelledby="quickAddCustomerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content card-modern border-0 shadow">
                <div class="modal-header border-bottom py-2.5 px-3 bg-light">
                    <h5 class="modal-title fs-6 fw-bold mb-0 text-dark" id="quickAddCustomerModalLabel">
                        <i class="fa-solid fa-user-plus text-primary me-2"></i> Register New Customer / Client
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form class="no-auto-save" @submit.prevent="submitQuickCustomer()">
                    <div class="modal-body p-3">
                        <div x-show="quickCustError" class="alert alert-danger py-2 px-3 small mb-3" x-text="quickCustError" x-cloak></div>
                        
                        <div class="row g-2.5">
                            <div class="col-md-7">
                                <label class="form-label small fw-semibold">Customer / Company Name *</label>
                                <input type="text" class="form-control form-control-sm" x-model="quickCust.name" required placeholder="e.g. Apex Tech Solutions Pvt Ltd" id="quick-cust-name-input">
                            </div>
                            <div class="col-md-5">
                                <label class="form-label small fw-semibold">Phone / Mobile</label>
                                <input type="text" class="form-control form-control-sm" x-model="quickCust.phone" placeholder="+91 98765 43210">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">GSTIN (15 Digits)</label>
                                <input type="text" class="form-control form-control-sm text-uppercase" x-model="quickCust.gstin" @input="onGstinInput()" maxlength="15" placeholder="e.g. 07AAAAA0000A1Z5">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Email Address</label>
                                <input type="email" class="form-control form-control-sm" x-model="quickCust.email" placeholder="billing@company.com">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">State *</label>
                                <input type="text" class="form-control form-control-sm" x-model="quickCust.state" required placeholder="Delhi">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">State Code *</label>
                                <input type="text" class="form-control form-control-sm text-center" x-model="quickCust.state_code" required maxlength="2" placeholder="07">
                            </div>
                            <div class="col-md-5">
                                <label class="form-label small fw-semibold">City</label>
                                <input type="text" class="form-control form-control-sm" x-model="quickCust.city" placeholder="New Delhi">
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-semibold">Billing Address</label>
                                <input type="text" class="form-control form-control-sm" x-model="quickCust.address" placeholder="Premises, Street, Area...">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Opening Balance (₹)</label>
                                <input type="number" step="0.01" class="form-control form-control-sm" x-model="quickCust.opening_balance" placeholder="0.00">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Balance Type</label>
                                <select class="form-select form-select-sm" x-model="quickCust.opening_balance_type">
                                    <option value="Dr">Dr (Receivable from customer)</option>
                                    <option value="Cr">Cr (Advance / Payable to customer)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-top py-2 px-3 bg-light">
                        <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-primary fw-semibold px-3" :disabled="quickCustSubmitting">
                            <span x-show="quickCustSubmitting" class="spinner-border spinner-border-sm me-1" role="status"></span>
                            <i class="fa-solid fa-check me-1" x-show="!quickCustSubmitting"></i>
                            <span>Save & Select Customer</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Ship To Address Modal (Saved only for this bill, never creates master customer) -->
    <div class="modal fade" id="shipToModal" tabindex="-1" aria-labelledby="shipToModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content card-modern border-0 shadow">
                <div class="modal-header border-bottom py-2.5 px-3 bg-light">
                    <h5 class="modal-title fs-6 fw-bold mb-0 text-dark" id="shipToModalLabel">
                        <i class="fa-solid fa-truck text-primary me-2"></i> Shipping / Consignee Address (For This Bill Only)
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form class="no-auto-save" @submit.prevent="saveShipTo()">
                    <div class="modal-body p-3">
                        <div class="alert alert-info py-2 px-3 small mb-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <div>
                                <i class="fa-solid fa-circle-info me-1"></i>
                                <span>These shipping details are associated <strong>with this bill only</strong> and will not modify or create customer masters.</span>
                            </div>
                            <button type="button" class="btn btn-xs btn-outline-primary" @click="copyBillingToShipping()">
                                <i class="fa-solid fa-clone me-1"></i> Copy from Selected Customer
                            </button>
                        </div>
                        
                        <div class="row g-2.5">
                            <div class="col-md-7">
                                <label class="form-label small fw-semibold">Consignee / Recipient Name *</label>
                                <input type="text" class="form-control form-control-sm" x-model="shipTo.name" required placeholder="e.g. Apex Tech Solutions (Warehouse 2)" id="shipto-name-input">
                            </div>
                            <div class="col-md-5">
                                <label class="form-label small fw-semibold">Phone / Mobile</label>
                                <input type="text" class="form-control form-control-sm" x-model="shipTo.phone" placeholder="+91 98765 43210">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">GSTIN (15 Digits)</label>
                                <input type="text" class="form-control form-control-sm text-uppercase" x-model="shipTo.gstin" @input="onShipToGstinInput()" maxlength="15" placeholder="e.g. 07AAAAA0000A1Z5">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Email Address</label>
                                <input type="email" class="form-control form-control-sm" x-model="shipTo.email" placeholder="shipping@company.com">
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-semibold">Shipping / Delivery Address *</label>
                                <input type="text" class="form-control form-control-sm" x-model="shipTo.address" required placeholder="Site, Warehouse, Street, Area...">
                            </div>
                            <div class="col-md-5">
                                <label class="form-label small fw-semibold">City</label>
                                <input type="text" class="form-control form-control-sm" x-model="shipTo.city" placeholder="Gurugram">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">State *</label>
                                <input type="text" class="form-control form-control-sm" x-model="shipTo.state" required placeholder="Haryana">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">State Code *</label>
                                <input type="text" class="form-control form-control-sm text-center" x-model="shipTo.state_code" required maxlength="2" placeholder="06">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Pincode</label>
                                <input type="text" class="form-control form-control-sm" x-model="shipTo.pincode" placeholder="122001">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-top py-2 px-3 bg-light d-flex justify-content-between">
                        <button type="button" class="btn btn-sm btn-outline-danger" @click="resetShipTo(); bootstrap.Modal.getInstance(document.getElementById('shipToModal')).hide();">
                            <i class="fa-solid fa-rotate-left me-1"></i> Use Billing Address
                        </button>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-sm btn-primary fw-semibold px-3">
                                <i class="fa-solid fa-check me-1"></i> Apply Shipping to Bill
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function invoiceForm() {
    return {
        customers: {!! json_encode($customers->map(function($c) {
            $label = $c->name . ($c->state ? ' (' . $c->state . ')' : '') . ($c->gstin ? ' [GST: ' . $c->gstin . ']' : '');
            return [
                'id' => (string) $c->id,
                'name' => $c->name,
                'state' => $c->state ?? '',
                'state_code' => $c->state_code ?? '',
                'gstin' => $c->gstin ?? '',
                'phone' => $c->phone ?? '',
                'city' => $c->city ?? '',
                'address' => $c->address ?? '',
                'pincode' => $c->pincode ?? '',
                'email' => $c->email ?? '',
                'label' => $label,
            ];
        })) !!},
        customerId: '{{ old('customer_ledger_id', '') }}',
        selectedCustomerLabel: '',
        isCustomerOpen: false,
        customerSearchQuery: '',
        filteredCustomers: [],
        customerHighlightIndex: 0,
        hasCustomShipTo: {{ old('shipping_name') ? 'true' : 'false' }},
        shipTo: {
            name: '{{ old('shipping_name', '') }}',
            phone: '{{ old('shipping_phone', '') }}',
            email: '{{ old('shipping_email', '') }}',
            gstin: '{{ old('shipping_gstin', '') }}',
            address: '{{ old('shipping_address', '') }}',
            city: '{{ old('shipping_city', '') }}',
            state: '{{ old('shipping_state', $company->state ?? 'Delhi') }}',
            state_code: '{{ old('shipping_state_code', $company->state_code ?? '07') }}',
            pincode: '{{ old('shipping_pincode', '') }}'
        },
        quickCust: {
            name: '',
            phone: '',
            email: '',
            gstin: '',
            state: '{{ $company->state ?? 'Delhi' }}',
            state_code: '{{ $company->state_code ?? '07' }}',
            city: '{{ $company->city ?? 'Delhi' }}',
            address: '',
            opening_balance: '0.00',
            opening_balance_type: 'Dr'
        },
        quickCustError: '',
        quickCustSubmitting: false,
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
                unit_symbol: '',
                isOpen: false,
                searchQuery: '',
                results: [],
                loading: false,
                highlightIndex: 0
            }
        ],
        init() {
            this.filteredCustomers = [...this.customers];
            if (this.customerId) {
                const found = this.customers.find(c => String(c.id) === String(this.customerId));
                if (found) {
                    this.selectedCustomerLabel = found.label;
                }
            }
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
                unit_symbol: '',
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
            this.isCustomerOpen = false;
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
            item.unit_symbol = prod.unit_symbol || '';
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
            item.unit_symbol = '';
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
            item.unit_symbol = '';
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
        toggleCustomerDropdown() {
            this.isCustomerOpen = !this.isCustomerOpen;
            if (this.isCustomerOpen) {
                this.items.forEach(it => it.isOpen = false);
                this.customerHighlightIndex = 0;
                this.customerSearchQuery = '';
                this.filteredCustomers = [...this.customers];
                this.$nextTick(() => {
                    const input = document.getElementById('customer-search-input');
                    if (input) {
                        input.focus();
                        input.select();
                    }
                });
            }
        },
        openCustomerDropdown() {
            this.items.forEach(it => it.isOpen = false);
            this.isCustomerOpen = true;
            this.customerHighlightIndex = 0;
            this.filteredCustomers = [...this.customers];
            this.$nextTick(() => {
                const input = document.getElementById('customer-search-input');
                if (input) {
                    input.focus();
                    input.select();
                }
            });
        },
        closeCustomerDropdown() {
            this.isCustomerOpen = false;
        },
        clearCustomer() {
            this.customerId = '';
            this.selectedCustomerLabel = '';
            this.customerSearchQuery = '';
            this.filteredCustomers = [...this.customers];
            this.onCustomerChange();
        },
        selectCustomer(cust) {
            if (!cust) return;
            this.customerId = cust.id;
            this.selectedCustomerLabel = cust.label || (cust.name + (cust.state ? ' (' + cust.state + ')' : ''));
            this.isCustomerOpen = false;
            this.customerSearchQuery = '';
            this.onCustomerChange();
        },
        onCustomerSearchInput() {
            const q = (this.customerSearchQuery || '').trim().toLowerCase();
            if (!q) {
                this.filteredCustomers = [...this.customers];
            } else {
                this.filteredCustomers = this.customers.filter(c => {
                    const nameMatch = (c.name || '').toLowerCase().includes(q);
                    const gstinMatch = (c.gstin || '').toLowerCase().includes(q);
                    const phoneMatch = (c.phone || '').includes(q);
                    const stateMatch = (c.state || '').toLowerCase().includes(q) || (c.state_code || '').includes(q);
                    const cityMatch = (c.city || '').toLowerCase().includes(q);
                    return nameMatch || gstinMatch || phoneMatch || stateMatch || cityMatch;
                });
            }
            this.customerHighlightIndex = 0;
        },
        onCustomerKeyDown(event) {
            if (event.key === 'ArrowDown') {
                if (this.filteredCustomers.length > 0) {
                    this.customerHighlightIndex = (this.customerHighlightIndex + 1) % this.filteredCustomers.length;
                    this.scrollCustomerHighlightedIntoView();
                }
            } else if (event.key === 'ArrowUp') {
                if (this.filteredCustomers.length > 0) {
                    this.customerHighlightIndex = (this.customerHighlightIndex - 1 + this.filteredCustomers.length) % this.filteredCustomers.length;
                    this.scrollCustomerHighlightedIntoView();
                }
            } else if (event.key === 'Enter') {
                if (this.filteredCustomers.length > 0 && this.filteredCustomers[this.customerHighlightIndex]) {
                    this.selectCustomer(this.filteredCustomers[this.customerHighlightIndex]);
                } else if (this.customerSearchQuery.trim().length > 0) {
                    this.openCustomerModal(this.customerSearchQuery.trim());
                }
            } else if (event.key === 'Escape') {
                this.closeCustomerDropdown();
            }
        },
        scrollCustomerHighlightedIntoView() {
            this.$nextTick(() => {
                const list = document.getElementById('customer-dropdown-list');
                if (!list) return;
                const rows = list.querySelectorAll('.product-combobox-row');
                if (rows[this.customerHighlightIndex]) {
                    rows[this.customerHighlightIndex].scrollIntoView({ block: 'nearest' });
                }
            });
        },
        openCustomerModal(prefillName = '') {
            this.isCustomerOpen = false;
            this.quickCustError = '';
            this.quickCust.name = prefillName || '';
            this.quickCust.phone = '';
            this.quickCust.email = '';
            this.quickCust.gstin = '';
            this.quickCust.state = '{{ $company->state ?? 'Delhi' }}';
            this.quickCust.state_code = '{{ $company->state_code ?? '07' }}';
            this.quickCust.city = '{{ $company->city ?? 'Delhi' }}';
            this.quickCust.address = '';
            this.quickCust.opening_balance = '0.00';
            this.quickCust.opening_balance_type = 'Dr';

            const modalEl = document.getElementById('quickAddCustomerModal');
            if (modalEl) {
                const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                modal.show();
                this.$nextTick(() => {
                    setTimeout(() => {
                        const input = document.getElementById('quick-cust-name-input');
                        if (input) input.focus();
                    }, 150);
                });
            }
        },
        onGstinInput() {
            const g = (this.quickCust.gstin || '').trim().toUpperCase();
            this.quickCust.gstin = g;
            if (g.length >= 2) {
                const code = g.substring(0, 2);
                const map = {
                    '01': 'Jammu & Kashmir', '02': 'Himachal Pradesh', '03': 'Punjab', '04': 'Chandigarh',
                    '05': 'Uttarakhand', '06': 'Haryana', '07': 'Delhi', '08': 'Rajasthan', '09': 'Uttar Pradesh',
                    '10': 'Bihar', '11': 'Sikkim', '12': 'Arunachal Pradesh', '13': 'Nagaland', '14': 'Manipur',
                    '15': 'Mizoram', '16': 'Tripura', '17': 'Meghalaya', '18': 'Assam', '19': 'West Bengal',
                    '20': 'Jharkhand', '21': 'Odisha', '22': 'Chhattisgarh', '23': 'Madhya Pradesh', '24': 'Gujarat',
                    '27': 'Maharashtra', '29': 'Karnataka', '30': 'Goa', '32': 'Kerala', '33': 'Tamil Nadu',
                    '36': 'Telangana', '37': 'Andhra Pradesh'
                };
                if (map[code]) {
                    this.quickCust.state_code = code;
                    this.quickCust.state = map[code];
                }
            }
        },
        submitQuickCustomer() {
            if (!this.quickCust.name.trim()) {
                this.quickCustError = 'Please enter customer name.';
                return;
            }
            this.quickCustSubmitting = true;
            this.quickCustError = '';

            fetch('{{ route('customers.store') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(this.quickCust)
            })
            .then(async res => {
                const data = await res.json();
                if (!res.ok) {
                    const msg = data.errors ? Object.values(data.errors).flat().join(' ') : (data.message || 'Failed to save customer');
                    throw new Error(msg);
                }
                return data;
            })
            .then(data => {
                this.quickCustSubmitting = false;
                if (data.customer) {
                    data.customer.id = String(data.customer.id);
                    this.customers.unshift(data.customer);
                    this.filteredCustomers = [...this.customers];
                    this.selectCustomer(data.customer);

                    const modalEl = document.getElementById('quickAddCustomerModal');
                    if (modalEl) {
                        const modal = bootstrap.Modal.getInstance(modalEl);
                        if (modal) modal.hide();
                    }
                }
            })
            .catch(err => {
                this.quickCustSubmitting = false;
                this.quickCustError = err.message || 'Error creating customer. Please check the input.';
            });
        },
        onCustomerChange() {
            // Customer selection trigger
        },
        openShipToModal() {
            this.isCustomerOpen = false;
            if (!this.hasCustomShipTo) {
                this.copyBillingToShipping();
            }

            const modalEl = document.getElementById('shipToModal');
            if (modalEl) {
                const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                modal.show();
                this.$nextTick(() => {
                    setTimeout(() => {
                        const input = document.getElementById('shipto-name-input');
                        if (input) input.focus();
                    }, 150);
                });
            }
        },
        copyBillingToShipping() {
            let cust = null;
            if (this.customerId) {
                cust = this.customers.find(c => String(c.id) === String(this.customerId));
            }
            if (cust) {
                this.shipTo.name = cust.name || '';
                this.shipTo.phone = cust.phone || '';
                this.shipTo.email = cust.email || '';
                this.shipTo.gstin = cust.gstin || '';
                this.shipTo.address = cust.address || '';
                this.shipTo.city = cust.city || '';
                this.shipTo.state = cust.state || '{{ $company->state ?? 'Delhi' }}';
                this.shipTo.state_code = cust.state_code || '{{ $company->state_code ?? '07' }}';
                this.shipTo.pincode = cust.pincode || '';
            } else {
                this.shipTo.state = '{{ $company->state ?? 'Delhi' }}';
                this.shipTo.state_code = '{{ $company->state_code ?? '07' }}';
            }
        },
        onShipToGstinInput() {
            const g = (this.shipTo.gstin || '').trim().toUpperCase();
            this.shipTo.gstin = g;
            if (g.length >= 2) {
                const code = g.substring(0, 2);
                const map = {
                    '01': 'Jammu & Kashmir', '02': 'Himachal Pradesh', '03': 'Punjab', '04': 'Chandigarh',
                    '05': 'Uttarakhand', '06': 'Haryana', '07': 'Delhi', '08': 'Rajasthan', '09': 'Uttar Pradesh',
                    '10': 'Bihar', '11': 'Sikkim', '12': 'Arunachal Pradesh', '13': 'Nagaland', '14': 'Manipur',
                    '15': 'Mizoram', '16': 'Tripura', '17': 'Meghalaya', '18': 'Assam', '19': 'West Bengal',
                    '20': 'Jharkhand', '21': 'Odisha', '22': 'Chhattisgarh', '23': 'Madhya Pradesh', '24': 'Gujarat',
                    '27': 'Maharashtra', '29': 'Karnataka', '30': 'Goa', '32': 'Kerala', '33': 'Tamil Nadu',
                    '36': 'Telangana', '37': 'Andhra Pradesh'
                };
                if (map[code]) {
                    this.shipTo.state_code = code;
                    this.shipTo.state = map[code];
                }
            }
        },
        saveShipTo() {
            if (!this.shipTo.name.trim()) {
                alert('Please enter consignee / recipient name');
                return;
            }
            this.hasCustomShipTo = true;
            const modalEl = document.getElementById('shipToModal');
            if (modalEl) {
                const modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();
            }
        },
        resetShipTo() {
            this.hasCustomShipTo = false;
            this.shipTo.name = '';
            this.shipTo.phone = '';
            this.shipTo.email = '';
            this.shipTo.gstin = '';
            this.shipTo.address = '';
            this.shipTo.city = '';
            this.shipTo.state = '{{ $company->state ?? 'Delhi' }}';
            this.shipTo.state_code = '{{ $company->state_code ?? '07' }}';
            this.shipTo.pincode = '';
        },
        submitForm() {
            if (!this.customerId) {
                this.openCustomerDropdown();
                alert('Please select or add a Customer / Client.');
                return;
            }
            document.getElementById('salesInvoiceForm').submit();
        }
    };
}
</script>
@endpush
