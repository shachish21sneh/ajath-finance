@extends('layouts.app')

@section('title', 'POS Retail Counter')

@section('content')
<div x-data="posBilling()" class="pos-screen">
    <!-- Left: Product Search & Cart Table -->
    <div class="d-flex flex-column h-100">
        <!-- Search & Barcode Ribbon -->
        <div class="card card-modern p-3 mb-3">
            <div class="row g-2 align-items-center">
                <div class="col-md-7">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-primary"><i class="fa-solid fa-barcode"></i></span>
                        <input type="text" id="posBarcodeScanner" class="form-control border-start-0" placeholder="Scan barcode or type SKU / item name..." x-model="searchQuery" @keydown.enter="handleBarcodeScan()">
                    </div>
                </div>
                <div class="col-md-5">
                    <select class="form-select" @change="addProductFromSelect($event)">
                        <option value="">-- Quick Click Item Lookup --</option>
                        @foreach($products as $p)
                            <option value="{{ $p->id }}" data-name="{{ $p->name }}" data-price="{{ $p->selling_price }}" data-tax="{{ $p->taxMaster->rate ?? 0 }}">
                                {{ $p->name }} - ₹ {{ number_format($p->selling_price, 2) }} ({{ $p->current_stock }} in stock)
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <!-- Cart Table -->
        <div class="card card-modern flex-grow-1 p-3 overflow-hidden d-flex flex-column">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="fw-bold mb-0"><i class="fa-solid fa-cart-shopping me-1 text-primary"></i> Current Cart (<span x-text="cart.length"></span> items)</h6>
                <button type="button" class="btn btn-sm btn-outline-danger" @click="clearCart()" x-show="cart.length > 0">Clear</button>
            </div>

            <div class="table-responsive flex-grow-1 overflow-y-auto">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small text-uppercase">
                        <tr>
                            <th>Item Particulars</th>
                            <th style="width: 140px;" class="text-center">Quantity</th>
                            <th style="width: 110px;" class="text-end">Price</th>
                            <th style="width: 120px;" class="text-end">Total</th>
                            <th style="width: 40px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(item, index) in cart" :key="index">
                            <tr>
                                <td>
                                    <div class="fw-bold small" x-text="item.name"></div>
                                    <div class="text-muted" style="font-size: 0.72rem;" x-text="'GST: ' + item.gst_rate + '%'"></div>
                                </td>
                                <td class="text-center">
                                    <div class="input-group input-group-sm justify-content-center" style="width: 110px; margin: 0 auto;">
                                        <button class="btn btn-outline-secondary" type="button" @click="decreaseQty(index)">-</button>
                                        <input type="number" class="form-control text-center p-0" x-model="item.qty" @input="recalcCart()">
                                        <button class="btn btn-outline-secondary" type="button" @click="increaseQty(index)">+</button>
                                    </div>
                                </td>
                                <td class="text-end small num-align" x-text="'₹ ' + formatNum(item.price)"></td>
                                <td class="text-end fw-bold num-align" x-text="'₹ ' + formatNum(item.qty * item.price)"></td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm text-danger p-0" @click="removeItem(index)">
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="cart.length === 0">
                            <td colspan="5" class="text-center text-muted py-5">
                                <i class="fa-solid fa-cash-register fa-2x mb-2 opacity-50"></i>
                                <p class="small mb-0">Cart is empty. Scan an item barcode or pick from dropdown.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Right: Checkout Settlement Panel -->
    <div class="pos-cart-panel h-100">
        <div class="p-3 border-bottom bg-light">
            <h6 class="fw-bold mb-0"><i class="fa-solid fa-receipt me-1 text-primary"></i> Checkout Settlement</h6>
        </div>

        <form id="posCheckoutForm" action="{{ route('sales.pos.store') }}" method="POST" class="p-3 flex-grow-1 d-flex flex-column justify-content-between">
            @csrf
            <div>
                <!-- Customer Selection -->
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Customer</label>
                    <select name="customer_ledger_id" class="form-select form-select-sm" x-model="selectedCustomerId" required>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}" {{ $loop->first ? 'selected' : '' }}>{{ $c->name }} ({{ $c->phone ?: 'Cash' }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold">Billing Date</label>
                    <input type="date" name="invoice_date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold">Payment Mode</label>
                    <div class="btn-group w-100" role="group">
                        <input type="radio" class="btn-check" name="payment_method" id="payCash" value="cash" checked x-model="paymentMethod">
                        <label class="btn btn-outline-primary btn-sm" for="payCash"><i class="fa-solid fa-money-bill me-1"></i> Cash</label>

                        <input type="radio" class="btn-check" name="payment_method" id="payUpi" value="upi" x-model="paymentMethod">
                        <label class="btn btn-outline-primary btn-sm" for="payUpi"><i class="fa-solid fa-qrcode me-1"></i> UPI</label>

                        <input type="radio" class="btn-check" name="payment_method" id="payCard" value="card" x-model="paymentMethod">
                        <label class="btn btn-outline-primary btn-sm" for="payCard"><i class="fa-solid fa-credit-card me-1"></i> Card</label>
                    </div>
                </div>

                <!-- Cash Tender Presets -->
                <div class="mb-3" x-show="paymentMethod === 'cash'">
                    <label class="form-label small fw-semibold">Cash Tendered (₹)</label>
                    <input type="number" step="1" name="cash_tendered" class="form-control form-control-lg text-end fw-bold text-success" x-model="cashTendered" @input="recalcChange()">
                    <div class="d-flex gap-1 mt-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary flex-grow-1" @click="setTender(totalAmount)">Exact</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary flex-grow-1" @click="setTender(500)">₹ 500</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary flex-grow-1" @click="setTender(1000)">₹ 1000</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary flex-grow-1" @click="setTender(2000)">₹ 2000</button>
                    </div>
                </div>

                <div class="p-3 bg-light rounded-3 mb-3" x-show="paymentMethod === 'cash' && changeReturned > 0">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-semibold text-danger">Change Return:</span>
                        <span class="fs-4 fw-bold text-danger num-align" x-text="'₹ ' + formatNum(changeReturned)"></span>
                    </div>
                </div>

                <!-- Hidden inputs for cart items -->
                <template x-for="(item, idx) in cart" :key="idx">
                    <div>
                        <input type="hidden" :name="`items[${idx}][product_id]`" :value="item.id">
                        <input type="hidden" :name="`items[${idx}][description]`" :value="item.name">
                        <input type="hidden" :name="`items[${idx}][quantity]`" :value="item.qty">
                        <input type="hidden" :name="`items[${idx}][unit_price]`" :value="item.price">
                        <input type="hidden" :name="`items[${idx}][gst_rate]`" :value="item.gst_rate">
                    </div>
                </template>
                <input type="hidden" name="paid_amount" :value="totalAmount">
            </div>

            <!-- Big Pay Button -->
            <div class="pt-3 border-top">
                <div class="d-flex justify-content-between py-1 small text-muted">
                    <span>Tax (GST Included):</span>
                    <span class="num-align" x-text="'₹ ' + formatNum(taxAmount)"></span>
                </div>
                <div class="d-flex justify-content-between py-2 border-top border-bottom fs-4 fw-bold text-main my-2">
                    <span>Payable:</span>
                    <span class="text-primary num-align" x-text="'₹ ' + formatNum(totalAmount)"></span>
                </div>

                <button type="submit" class="btn btn-success w-100 py-3 fw-bold fs-6 shadow" :disabled="cart.length === 0">
                    <i class="fa-solid fa-print me-2"></i> Complete Sale & Receipt
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function posBilling() {
    return {
        searchQuery: '',
        selectedCustomerId: '{{ $customers->first()?->id ?? 1 }}',
        paymentMethod: 'cash',
        cashTendered: 0,
        changeReturned: 0,
        cart: [],
        productsList: @json($products),
        get totalAmount() {
            return this.cart.reduce((sum, item) => sum + (item.qty * item.price), 0);
        },
        get taxAmount() {
            return this.cart.reduce((sum, item) => {
                const itemTotal = item.qty * item.price;
                const tax = (itemTotal * item.gst_rate) / (100 + item.gst_rate);
                return sum + tax;
            }, 0);
        },
        formatNum(n) {
            return (n || 0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        },
        handleBarcodeScan() {
            const query = this.searchQuery.trim().toLowerCase();
            if (!query) return;

            const found = this.productsList.find(p =>
                (p.barcode && p.barcode.toLowerCase() === query) ||
                (p.sku && p.sku.toLowerCase() === query) ||
                p.name.toLowerCase().includes(query)
            );

            if (found) {
                this.addItemToCart(found);
                this.searchQuery = '';
            } else {
                alert('No product found for scanned code: ' + query);
            }
        },
        addProductFromSelect(e) {
            const id = e.target.value;
            if (!id) return;
            const found = this.productsList.find(p => p.id == id);
            if (found) {
                this.addItemToCart(found);
                e.target.value = '';
            }
        },
        addItemToCart(product) {
            const existing = this.cart.find(c => c.id === product.id);
            if (existing) {
                existing.qty++;
            } else {
                this.cart.push({
                    id: product.id,
                    name: product.name,
                    price: parseFloat(product.selling_price) || 0,
                    gst_rate: parseFloat(product.tax_master?.rate || 18),
                    qty: 1
                });
            }
            this.recalcChange();
        },
        increaseQty(i) {
            this.cart[i].qty++;
            this.recalcChange();
        },
        decreaseQty(i) {
            if (this.cart[i].qty > 1) {
                this.cart[i].qty--;
            } else {
                this.cart.splice(i, 1);
            }
            this.recalcChange();
        },
        removeItem(i) {
            this.cart.splice(i, 1);
            this.recalcChange();
        },
        clearCart() {
            if (confirm('Clear entire cart?')) {
                this.cart = [];
                this.cashTendered = 0;
                this.changeReturned = 0;
            }
        },
        setTender(amount) {
            this.cashTendered = amount;
            this.recalcChange();
        },
        recalcChange() {
            const tendered = parseFloat(this.cashTendered) || 0;
            this.changeReturned = Math.max(0, tendered - this.totalAmount);
        }
    };
}
</script>
@endpush
