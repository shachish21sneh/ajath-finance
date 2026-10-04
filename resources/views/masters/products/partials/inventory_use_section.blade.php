<!-- Inventory Use / Linked Components Section -->
<style>
.searchable-dropdown-menu {
    position: absolute;
    top: calc(100% + 4px);
    left: 0;
    right: 0;
    z-index: 1060;
    background: #ffffff;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.15), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
    max-height: 350px;
    display: none;
    flex-direction: column;
}
.searchable-dropdown-item {
    padding: 10px 14px;
    cursor: pointer;
    border-bottom: 1px solid #f1f5f9;
    transition: background-color 0.15s ease, transform 0.1s ease;
}
.searchable-dropdown-item:hover, .searchable-dropdown-item.active {
    background-color: #f0f7ff;
}
.searchable-dropdown-item:last-child {
    border-bottom: none;
}
.searchable-dropdown-scroll {
    overflow-y: auto;
    max-height: 280px;
}
.row-highlight {
    animation: highlightFade 1.8s ease-out;
}
@keyframes highlightFade {
    0% { background-color: #dbeafe; }
    100% { background-color: transparent; }
}
</style>

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

        <!-- Product Selector Ribbon with in-dropdown search -->
        <div class="bg-light p-3 rounded-3 border mb-3">
            <div class="row g-2 align-items-end">
                <div class="col-lg-8 col-md-7">
                    <label class="form-label small fw-semibold text-muted mb-1 d-flex justify-content-between">
                        <span><i class="fa-solid fa-magnifying-glass text-primary me-1"></i> Search & Pick Inventory Product to Link</span>
                        <span class="text-muted" style="font-size: 0.72rem;">Type to search or click to browse</span>
                    </label>

                    <div class="position-relative" id="searchable_component_picker">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 text-muted">
                                <i class="fa-solid fa-search"></i>
                            </span>
                            <input type="text" id="component_search_input"
                                   class="form-control border-start-0"
                                   placeholder="Type product name, SKU, or HSN to search & add..."
                                   autocomplete="off"
                                   onfocus="openComponentDropdown()"
                                   oninput="filterComponentDropdown(this.value)"
                                   onkeydown="handleComponentSearchKeydown(event)">
                            <button class="btn btn-outline-secondary bg-white border-start-0 text-muted" type="button" onclick="toggleComponentDropdown()" title="Show/hide products list">
                                <i class="fa-solid fa-chevron-down small" id="component_dropdown_icon"></i>
                            </button>
                        </div>

                        <!-- Dropdown Menu with Live Search Filter -->
                        <div id="component_dropdown_menu" class="searchable-dropdown-menu">
                            <div class="p-2 border-bottom bg-light">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-white"><i class="fa-solid fa-filter text-muted"></i></span>
                                    <input type="text" id="component_inner_search_input" class="form-control" placeholder="Filter in list..." oninput="filterComponentDropdown(this.value)">
                                    <button class="btn btn-outline-secondary" type="button" onclick="clearComponentSearch()" title="Clear">
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-1 px-1">
                                    <span class="text-muted" style="font-size: 0.70rem;">Click any item below to add:</span>
                                    <span class="badge bg-secondary-subtle text-secondary" id="filtered_count_badge" style="font-size: 0.70rem;">{{ count($availableProducts ?? []) }} products</span>
                                </div>
                            </div>
                            <div class="searchable-dropdown-scroll" id="component_dropdown_list">
                                @if(isset($availableProducts) && count($availableProducts))
                                    @foreach($availableProducts as $ap)
                                        <div class="searchable-dropdown-item d-flex align-items-center justify-content-between"
                                             data-id="{{ $ap->id }}"
                                             data-name="{{ $ap->name }}"
                                             data-sku="{{ $ap->sku ?? '' }}"
                                             data-hsn="{{ $ap->hsn_code ?: $ap->sac_code }}"
                                             data-tax="{{ $ap->taxMaster->rate ?? 0 }}"
                                             data-price="{{ $ap->purchase_price > 0 ? $ap->purchase_price : $ap->selling_price }}"
                                             data-stock="{{ $ap->current_stock }}"
                                             data-unit="{{ $ap->unit->symbol ?? 'PCS' }}"
                                             onclick="pickComponentFromItem(this)">
                                            <div class="me-2 text-truncate">
                                                <div class="fw-bold text-dark text-truncate">{{ $ap->name }}</div>
                                                <div class="text-muted small" style="font-size: 0.75rem;">
                                                    @if($ap->sku)<span class="me-2"><i class="fa-solid fa-barcode text-muted me-1"></i>{{ $ap->sku }}</span>@endif
                                                    @if($ap->hsn_code || $ap->sac_code)<span>HSN: {{ $ap->hsn_code ?: $ap->sac_code }}</span>@endif
                                                </div>
                                            </div>
                                            <div class="text-end text-nowrap ms-2">
                                                <span class="badge {{ $ap->current_stock > 0 ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-muted' }} border small">
                                                    {{ $ap->current_stock }} {{ $ap->unit->symbol ?? '' }}
                                                </span>
                                                <div class="fw-bold text-primary font-monospace small mt-1">₹ {{ number_format($ap->purchase_price > 0 ? $ap->purchase_price : $ap->selling_price, 2) }}</div>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="text-center py-3 text-muted small">No available products found.</div>
                                @endif
                                <div id="component_dropdown_no_match" class="text-center py-4 text-muted small d-none">
                                    <i class="fa-solid fa-box-open fs-4 mb-2 text-muted opacity-50"></i>
                                    <div>No matching products found.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Managed Buttons (Clean, Responsive, No Wrapping) -->
                <div class="col-lg-4 col-md-5 d-flex gap-2">
                    <button type="button" class="btn btn-primary text-nowrap flex-grow-1 fw-semibold" onclick="addFirstMatchingOrSelected()" title="Add product to components list">
                        <i class="fa-solid fa-plus me-1"></i> Add Item
                    </button>
                    <button type="button" class="btn btn-outline-secondary text-nowrap fw-semibold px-3" onclick="addBlankComponentRow()" title="Add custom row">
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
            <div class="small text-muted">Search and pick a product from the dropdown above to link components.</div>
        </div>

        <!-- Component Summary Strip -->
        <div class="d-flex flex-wrap align-items-center justify-content-between p-3 bg-light rounded-3 border gap-2">
            <div class="d-flex align-items-center gap-3">
                <span class="small text-muted">Linked Items: <strong id="comp_total_count" class="text-dark">0</strong></span>
                <span class="text-muted">|</span>
                <span class="small text-muted">Total Components Cost: <strong id="comp_total_cost" class="text-primary font-monospace fs-6">₹ 0.00</strong></span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-outline-danger btn-sm text-nowrap" id="comp_clear_btn" onclick="clearAllComponents()" style="display: none;">
                    <i class="fa-solid fa-trash-can me-1"></i> Clear All
                </button>
                <button type="button" class="btn btn-outline-primary btn-sm text-nowrap fw-semibold" onclick="applyCostToPurchasePrice()">
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
            addComponentRow(item, false);
        });
    } else {
        updateEmptyState();
    }

    // Close dropdown on click outside
    document.addEventListener('click', function(e) {
        const picker = document.getElementById('searchable_component_picker');
        if (picker && !picker.contains(e.target)) {
            closeComponentDropdown();
        }
    });
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

// Searchable Dropdown Functions
function openComponentDropdown() {
    const menu = document.getElementById('component_dropdown_menu');
    if (menu) {
        menu.style.display = 'flex';
        const icon = document.getElementById('component_dropdown_icon');
        if (icon) {
            icon.classList.remove('fa-chevron-down');
            icon.classList.add('fa-chevron-up');
        }
    }
}

function closeComponentDropdown() {
    const menu = document.getElementById('component_dropdown_menu');
    if (menu) {
        menu.style.display = 'none';
        const icon = document.getElementById('component_dropdown_icon');
        if (icon) {
            icon.classList.remove('fa-chevron-up');
            icon.classList.add('fa-chevron-down');
        }
    }
}

function toggleComponentDropdown() {
    const menu = document.getElementById('component_dropdown_menu');
    if (menu && menu.style.display === 'flex') {
        closeComponentDropdown();
    } else {
        openComponentDropdown();
    }
}

function filterComponentDropdown(query) {
    openComponentDropdown();
    const q = (query || '').toLowerCase().trim();

    // Sync inner and outer search inputs
    const mainInput = document.getElementById('component_search_input');
    const innerInput = document.getElementById('component_inner_search_input');
    if (mainInput && mainInput.value !== query && document.activeElement !== mainInput) {
        mainInput.value = query;
    }
    if (innerInput && innerInput.value !== query && document.activeElement !== innerInput) {
        innerInput.value = query;
    }

    const items = document.querySelectorAll('.searchable-dropdown-item');
    let visibleCount = 0;

    items.forEach(el => {
        const name = (el.getAttribute('data-name') || '').toLowerCase();
        const sku = (el.getAttribute('data-sku') || '').toLowerCase();
        const hsn = (el.getAttribute('data-hsn') || '').toLowerCase();

        if (!q || name.includes(q) || sku.includes(q) || hsn.includes(q)) {
            el.classList.remove('d-none');
            visibleCount++;
        } else {
            el.classList.add('d-none');
        }
    });

    const noMatch = document.getElementById('component_dropdown_no_match');
    if (noMatch) {
        if (visibleCount === 0) {
            noMatch.classList.remove('d-none');
        } else {
            noMatch.classList.add('d-none');
        }
    }

    const badge = document.getElementById('filtered_count_badge');
    if (badge) {
        badge.textContent = `${visibleCount} products`;
    }
}

function clearComponentSearch() {
    const mainInput = document.getElementById('component_search_input');
    const innerInput = document.getElementById('component_inner_search_input');
    if (mainInput) mainInput.value = '';
    if (innerInput) innerInput.value = '';
    filterComponentDropdown('');
}

function pickComponentFromItem(el) {
    const item = {
        component_product_id: el.getAttribute('data-id'),
        name: el.getAttribute('data-name') || '',
        hsn_code: el.getAttribute('data-hsn') || '',
        gst_rate: parseFloat(el.getAttribute('data-tax')) || 0,
        unit_price: parseFloat(el.getAttribute('data-price')) || 0,
        quantity: 1,
        stock: el.getAttribute('data-stock') || '0',
        unit: el.getAttribute('data-unit') || 'PCS'
    };

    addComponentRow(item, true);
    closeComponentDropdown();
    clearComponentSearch();
}

function handleComponentSearchKeydown(event) {
    if (event.key === 'Enter') {
        event.preventDefault();
        addFirstMatchingOrSelected();
    } else if (event.key === 'Escape') {
        closeComponentDropdown();
    }
}

function addFirstMatchingOrSelected() {
    const firstVisible = document.querySelector('.searchable-dropdown-item:not(.d-none)');
    if (firstVisible) {
        pickComponentFromItem(firstVisible);
    } else {
        const query = (document.getElementById('component_search_input').value || '').trim();
        if (query) {
            addComponentRow({
                component_product_id: '',
                name: query,
                hsn_code: '',
                gst_rate: 0,
                unit_price: 0,
                quantity: 1,
                stock: '-',
                unit: 'PCS'
            }, true);
            clearComponentSearch();
            closeComponentDropdown();
        } else {
            addBlankComponentRow();
        }
    }
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
    }, true);
}

function addComponentRow(data, shouldHighlight = true) {
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

    if (shouldHighlight) {
        setTimeout(() => {
            const addedRow = document.getElementById(rowId);
            if (addedRow) {
                addedRow.classList.add('row-highlight');
            }
        }, 50);
    }
}

function removeComponentRow(rowId) {
    componentRows = componentRows.filter(r => r.id !== rowId);
    renderComponentRows();
}

function clearAllComponents() {
    if (componentRows.length === 0) return;
    if (confirm('Are you sure you want to remove all linked components?')) {
        componentRows = [];
        renderComponentRows();
    }
}

function renderComponentRows() {
    const tbody = document.getElementById('components_tbody');
    tbody.innerHTML = '';

    const clearBtn = document.getElementById('comp_clear_btn');
    if (clearBtn) {
        clearBtn.style.display = componentRows.length > 0 ? 'inline-block' : 'none';
    }

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
                       class="form-control form-control-sm text-end font-monospace"
                       value="${row.unit_price}"
                       oninput="updateRowField('${row.id}', 'unit_price', this.value)">
            </td>
            <td>
                <input type="number" step="0.01" min="0.01" name="components[${i}][quantity]"
                       class="form-control form-control-sm text-end font-monospace"
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
