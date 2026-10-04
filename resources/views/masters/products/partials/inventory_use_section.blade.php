<!-- Inventory Use / Linked Components Section -->
<div class="card card-modern border-top border-primary border-3 mt-4 mb-3 p-3 p-md-4 shadow-sm">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 pb-3 border-bottom">
        <div class="form-check form-switch mb-0">
            <input class="form-check-input fs-5" type="checkbox" role="switch" id="inventory_use_toggle"
                   name="has_inventory_components" value="1"
                   {{ old('has_inventory_components', $product->has_inventory_components ?? false) ? 'checked' : '' }}
                   onchange="toggleInventoryUseSection(this.checked)">
            <label class="form-check-label fw-bold text-main fs-6 ms-2" for="inventory_use_toggle">
                <i class="fa-solid fa-boxes-stacked text-primary me-2"></i> Inventory Use (Kit / Bundle / BOM Components)
            </label>
        </div>
        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill small">
            <i class="fa-solid fa-sync-alt me-1"></i> Automated Multi-Stock Management
        </span>
    </div>

    <div id="inventory_use_container" class="{{ old('has_inventory_components', $product->has_inventory_components ?? false) ? '' : 'd-none' }} pt-3">
        <p class="text-muted small mb-3">
            <i class="fa-solid fa-circle-info text-info me-1"></i>
            Link underlying inventory items/materials to this product. When this product is sold, stock for both this item and each linked component will be automatically deducted.
        </p>

        <!-- Product Selector Ribbon -->
        <div class="bg-light p-3 rounded-3 border mb-3">
            <div class="row g-2 align-items-center">
                <div class="col-md-7">
                    <label class="form-label small fw-semibold text-muted mb-1">Search & Pick Inventory Product to Link</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                        <select id="component_picker_select" class="form-select border-start-0">
                            <option value="">-- Choose from existing products --</option>
                            @if(isset($availableProducts))
                                @foreach($availableProducts as $ap)
                                    <option value="{{ $ap->id }}"
                                            data-id="{{ $ap->id }}"
                                            data-name="{{ $ap->name }}"
                                            data-hsn="{{ $ap->hsn_code ?: $ap->sac_code }}"
                                            data-tax="{{ $ap->taxMaster->rate ?? 0 }}"
                                            data-price="{{ $ap->purchase_price > 0 ? $ap->purchase_price : $ap->selling_price }}"
                                            data-stock="{{ $ap->current_stock }}"
                                            data-unit="{{ $ap->unit->symbol ?? 'PCS' }}">
                                        {{ $ap->name }} &bull; [Stock: {{ $ap->current_stock }} {{ $ap->unit->symbol ?? '' }}] &bull; ₹ {{ number_format($ap->purchase_price > 0 ? $ap->purchase_price : $ap->selling_price, 2) }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                </div>
                <div class="col-md-5 d-flex align-items-end gap-2 mt-2 mt-md-0">
                    <button type="button" class="btn btn-sm btn-primary flex-grow-1" onclick="addSelectedComponent()">
                        <i class="fa-solid fa-plus me-1"></i> Add to Components List
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="addBlankComponentRow()" title="Add Custom Item Row">
                        <i class="fa-solid fa-pen-nib me-1"></i> Custom Row
                    </button>
                </div>
            </div>
        </div>

        <!-- Components Table -->
        <div class="table-responsive border rounded-3 bg-white mb-3">
            <table class="table table-hover align-middle mb-0" id="components_table">
                <thead class="table-light small text-uppercase text-muted">
                    <tr>
                        <th style="width: 40px;" class="text-center">#</th>
                        <th style="min-width: 220px;">Product Name *</th>
                        <th style="width: 130px;">HSN / SAC</th>
                        <th style="width: 120px;">GST Rate (%)</th>
                        <th style="width: 130px;" class="text-end">Unit Price (₹)</th>
                        <th style="width: 110px;" class="text-end">Qty Used *</th>
                        <th style="width: 120px;" class="text-center">Current Stock</th>
                        <th style="width: 130px;" class="text-end">Total (₹)</th>
                        <th style="width: 50px;" class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody id="components_tbody">
                    <!-- Populated dynamically via JS -->
                </tbody>
            </table>
        </div>

        <!-- Empty State Message -->
        <div id="components_empty_msg" class="text-center py-4 text-muted bg-white border rounded-3 mb-3 d-none">
            <i class="fa-solid fa-boxes-packing fs-2 text-muted opacity-50 mb-2"></i>
            <div class="small fw-semibold">No linked inventory items added yet.</div>
            <div class="small text-muted">Pick a product from the dropdown above and click "Add to Components List".</div>
        </div>

        <!-- Component Summary Strip -->
        <div class="d-flex flex-wrap align-items-center justify-content-between p-3 bg-light rounded-3 border">
            <div class="d-flex align-items-center gap-3">
                <span class="small text-muted">Linked Items: <strong id="comp_total_count" class="text-dark">0</strong></span>
                <span class="text-muted">|</span>
                <span class="small text-muted">Total Components Cost: <strong id="comp_total_cost" class="text-primary font-monospace fs-6">₹ 0.00</strong></span>
            </div>
            <div>
                <button type="button" class="btn btn-xs btn-outline-primary btn-sm" onclick="applyCostToPurchasePrice()">
                    <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Apply as Purchase Price
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let componentRows = [];

// Initialize existing components on page load
document.addEventListener('DOMContentLoaded', function() {
    @php
        $initialComponents = [];
        if (old('components')) {
            $initialComponents = old('components');
        } elseif (isset($product) && $product->inventoryComponents) {
            foreach ($product->inventoryComponents as $c) {
                $initialComponents[] = [
                    'component_product_id' => $c->component_product_id,
                    'name' => $c->name,
                    'hsn_code' => $c->hsn_code,
                    'gst_rate' => (float)$c->gst_rate,
                    'unit_price' => (float)$c->unit_price,
                    'quantity' => (float)$c->quantity,
                    'stock' => $c->componentProduct->current_stock ?? '0',
                    'unit' => $c->componentProduct->unit->symbol ?? 'PCS'
                ];
            }
        }
    @endphp

    const initialData = @json($initialComponents);
    if (initialData && initialData.length > 0) {
        initialData.forEach(item => {
            addComponentRow(item);
        });
    } else {
        updateEmptyState();
    }
});

function toggleInventoryUseSection(show) {
    const container = document.getElementById('inventory_use_container');
    if (show) {
        container.classList.remove('d-none');
        if (componentRows.length === 0) {
            updateEmptyState();
        }
    } else {
        container.classList.add('d-none');
    }
}

function addSelectedComponent() {
    const select = document.getElementById('component_picker_select');
    const selectedOpt = select.options[select.selectedIndex];
    if (!selectedOpt || !selectedOpt.value) {
        alert('Please choose a product from the dropdown to add.');
        return;
    }

    const item = {
        component_product_id: selectedOpt.value,
        name: selectedOpt.getAttribute('data-name') || '',
        hsn_code: selectedOpt.getAttribute('data-hsn') || '',
        gst_rate: parseFloat(selectedOpt.getAttribute('data-tax')) || 0,
        unit_price: parseFloat(selectedOpt.getAttribute('data-price')) || 0,
        quantity: 1,
        stock: selectedOpt.getAttribute('data-stock') || '0',
        unit: selectedOpt.getAttribute('data-unit') || 'PCS'
    };

    addComponentRow(item);
    select.value = '';
}

function addBlankComponentRow() {
    addComponentRow({
        component_product_id: '',
        name: '',
        hsn_code: '',
        gst_rate: 0,
        unit_price: 0,
        quantity: 1,
        stock: '-',
        unit: 'PCS'
    });
}

function addComponentRow(data) {
    const index = componentRows.length;
    const rowId = `comp_row_${Date.now()}_${Math.floor(Math.random() * 1000)}`;

    const rowObj = {
        id: rowId,
        index: index,
        component_product_id: data.component_product_id || '',
        name: data.name || '',
        hsn_code: data.hsn_code || '',
        gst_rate: data.gst_rate !== undefined ? data.gst_rate : 0,
        unit_price: data.unit_price !== undefined ? data.unit_price : 0,
        quantity: data.quantity !== undefined ? data.quantity : 1,
        stock: data.stock !== undefined ? data.stock : '-',
        unit: data.unit || 'PCS'
    };

    componentRows.push(rowObj);
    renderComponentRows();
}

function removeComponentRow(rowId) {
    componentRows = componentRows.filter(r => r.id !== rowId);
    renderComponentRows();
}

function renderComponentRows() {
    const tbody = document.getElementById('components_tbody');
    tbody.innerHTML = '';

    if (componentRows.length === 0) {
        updateEmptyState();
        recalcComponentTotals();
        return;
    }

    document.getElementById('components_empty_msg').classList.add('d-none');
    document.getElementById('components_table').classList.remove('d-none');

    componentRows.forEach((row, i) => {
        const tr = document.createElement('tr');
        tr.id = row.id;

        const subtotal = (parseFloat(row.quantity) || 0) * (parseFloat(row.unit_price) || 0);

        tr.innerHTML = `
            <td class="text-center text-muted small fw-bold">${i + 1}</td>
            <td>
                <input type="hidden" name="components[${i}][component_product_id]" value="${row.component_product_id}">
                <input type="text" name="components[${i}][name]" class="form-control form-control-sm"
                       value="${escapeHtml(row.name)}" placeholder="Item Name" required
                       oninput="updateRowField('${row.id}', 'name', this.value)">
            </td>
            <td>
                <input type="text" name="components[${i}][hsn_code]" class="form-control form-control-sm"
                       value="${escapeHtml(row.hsn_code)}" placeholder="HSN/SAC"
                       oninput="updateRowField('${row.id}', 'hsn_code', this.value)">
            </td>
            <td>
                <select name="components[${i}][gst_rate]" class="form-select form-select-sm"
                        onchange="updateRowField('${row.id}', 'gst_rate', this.value)">
                    <option value="0" ${row.gst_rate == 0 ? 'selected' : ''}>0%</option>
                    <option value="5" ${row.gst_rate == 5 ? 'selected' : ''}>5%</option>
                    <option value="12" ${row.gst_rate == 12 ? 'selected' : ''}>12%</option>
                    <option value="18" ${row.gst_rate == 18 ? 'selected' : ''}>18%</option>
                    <option value="28" ${row.gst_rate == 28 ? 'selected' : ''}>28%</option>
                </select>
            </td>
            <td>
                <input type="number" step="0.01" min="0" name="components[${i}][unit_price]"
                       class="form-control form-control-sm text-end"
                       value="${row.unit_price}"
                       oninput="updateRowField('${row.id}', 'unit_price', this.value)">
            </td>
            <td>
                <input type="number" step="0.01" min="0.01" name="components[${i}][quantity]"
                       class="form-control form-control-sm text-end"
                       value="${row.quantity}" required
                       oninput="updateRowField('${row.id}', 'quantity', this.value)">
            </td>
            <td class="text-center">
                <span class="badge ${parseFloat(row.stock) > 0 ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-muted'} border small">
                    ${row.stock} ${row.unit || ''}
                </span>
            </td>
            <td class="text-end fw-bold font-monospace small">
                ₹ <span id="subtotal_${row.id}">${subtotal.toFixed(2)}</span>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2" onclick="removeComponentRow('${row.id}')" title="Remove Component">
                    <i class="fa-solid fa-trash-can small"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    });

    recalcComponentTotals();
}

function updateRowField(rowId, field, value) {
    const row = componentRows.find(r => r.id === rowId);
    if (!row) return;

    row[field] = value;

    if (field === 'quantity' || field === 'unit_price') {
        const subtotal = (parseFloat(row.quantity) || 0) * (parseFloat(row.unit_price) || 0);
        const subtotalEl = document.getElementById(`subtotal_${row.id}`);
        if (subtotalEl) {
            subtotalEl.textContent = subtotal.toFixed(2);
        }
        recalcComponentTotals();
    }
}

function updateEmptyState() {
    const emptyMsg = document.getElementById('components_empty_msg');
    const table = document.getElementById('components_table');
    if (componentRows.length === 0) {
        if (emptyMsg) emptyMsg.classList.remove('d-none');
        if (table) table.classList.add('d-none');
    } else {
        if (emptyMsg) emptyMsg.classList.add('d-none');
        if (table) table.classList.remove('d-none');
    }
}

function recalcComponentTotals() {
    let totalCost = 0;
    componentRows.forEach(r => {
        const qty = parseFloat(r.quantity) || 0;
        const price = parseFloat(r.unit_price) || 0;
        totalCost += (qty * price);
    });

    const countEl = document.getElementById('comp_total_count');
    if (countEl) countEl.textContent = componentRows.length;

    const costEl = document.getElementById('comp_total_cost');
    if (costEl) costEl.textContent = '₹ ' + totalCost.toFixed(2);
}

function applyCostToPurchasePrice() {
    let totalCost = 0;
    componentRows.forEach(r => {
        const qty = parseFloat(r.quantity) || 0;
        const price = parseFloat(r.unit_price) || 0;
        totalCost += (qty * price);
    });

    const purchaseInput = document.querySelector('input[name="purchase_price"]');
    if (purchaseInput) {
        purchaseInput.value = totalCost.toFixed(2);
        alert(`Purchase Price updated to ₹ ${totalCost.toFixed(2)} based on total components cost.`);
    }
}

function escapeHtml(text) {
    if (!text) return '';
    return text.toString()
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}
</script>
