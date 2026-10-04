<!-- Quick Create Stock Group Modal -->
<div class="modal fade" id="quickAddStockGroupModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow border-0">
            <form id="quickAddStockGroupForm" onsubmit="handleQuickCreateStockGroup(event)">
                @csrf
                <div class="modal-header bg-light">
                    <h6 class="modal-title fw-bold text-main">
                        <i class="fa-solid fa-layer-group text-primary me-2"></i> Create New Stock Group
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Group Name *</label>
                        <input type="text" id="quick_group_name" name="name" class="form-control" placeholder="e.g. Energy Storage & Batteries" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-semibold">Parent Group (Optional)</label>
                        <select id="quick_group_parent_id" name="parent_id" class="form-select">
                            <option value="">-- None (Primary Group) --</option>
                            @foreach($groups as $g)
                                <option value="{{ $g->id }}">{{ $g->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div id="quickGroupError" class="text-danger small mt-2 d-none"></div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" id="quickGroupSubmitBtn" class="btn btn-sm btn-primary px-3 fw-semibold">
                        <i class="fa-solid fa-check me-1"></i> Save & Select
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Quick Create Unit Modal -->
<div class="modal fade" id="quickAddUnitModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow border-0">
            <form id="quickAddUnitForm" onsubmit="handleQuickCreateUnit(event)">
                @csrf
                <div class="modal-header bg-light">
                    <h6 class="modal-title fw-bold text-main">
                        <i class="fa-solid fa-scale-unbalanced text-success me-2"></i> Create New Measurement Unit
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Unit Name *</label>
                        <input type="text" id="quick_unit_name" name="name" class="form-control" placeholder="e.g. Numbers, Square Feet, Kilograms" required>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-7">
                            <label class="form-label small fw-semibold">Symbol / Code *</label>
                            <input type="text" id="quick_unit_symbol" name="symbol" class="form-control" placeholder="e.g. NOS, SQFT, KGS" required>
                        </div>
                        <div class="col-5">
                            <label class="form-label small fw-semibold">Decimals</label>
                            <select id="quick_unit_decimals" name="decimal_places" class="form-select">
                                <option value="0">0 (Whole)</option>
                                <option value="1">1 (0.1)</option>
                                <option value="2">2 (0.01)</option>
                                <option value="3">3 (0.001)</option>
                            </select>
                        </div>
                    </div>
                    <div id="quickUnitError" class="text-danger small mt-2 d-none"></div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" id="quickUnitSubmitBtn" class="btn btn-sm btn-success px-3 fw-semibold text-white">
                        <i class="fa-solid fa-check me-1"></i> Save & Select
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Quick Create Tax Rate Modal -->
<div class="modal fade" id="quickAddTaxModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow border-0">
            <form id="quickAddTaxForm" onsubmit="handleQuickCreateTax(event)">
                @csrf
                <div class="modal-header bg-light">
                    <h6 class="modal-title fw-bold text-main">
                        <i class="fa-solid fa-percent text-warning me-2"></i> Create New Applicable GST Rate
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Tax Name *</label>
                        <input type="text" id="quick_tax_name" name="name" class="form-control" placeholder="e.g. GST 28% or GST 12%" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Total GST Rate (%) *</label>
                            <input type="number" step="0.01" min="0" max="100" id="quick_tax_rate" name="rate" class="form-control" placeholder="e.g. 28" required oninput="autoCalcQuickTax()">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Cess Rate (%)</label>
                            <input type="number" step="0.01" min="0" id="quick_tax_cess" name="cess_rate" class="form-control" value="0.00">
                        </div>
                    </div>
                    <div class="row g-2 mb-2 bg-light p-2 rounded small">
                        <div class="col-4">
                            <label class="form-label small text-muted mb-1">CGST (%)</label>
                            <input type="number" step="0.01" id="quick_tax_cgst" name="cgst_rate" class="form-control form-control-sm" readonly>
                        </div>
                        <div class="col-4">
                            <label class="form-label small text-muted mb-1">SGST (%)</label>
                            <input type="number" step="0.01" id="quick_tax_sgst" name="sgst_rate" class="form-control form-control-sm" readonly>
                        </div>
                        <div class="col-4">
                            <label class="form-label small text-muted mb-1">IGST (%)</label>
                            <input type="number" step="0.01" id="quick_tax_igst" name="igst_rate" class="form-control form-control-sm" readonly>
                        </div>
                    </div>
                    <div id="quickTaxError" class="text-danger small mt-2 d-none"></div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" id="quickTaxSubmitBtn" class="btn btn-sm btn-primary px-3 fw-semibold">
                        <i class="fa-solid fa-check me-1"></i> Save & Select
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function autoCalcQuickTax() {
    const rate = parseFloat(document.getElementById('quick_tax_rate').value) || 0;
    document.getElementById('quick_tax_cgst').value = (rate / 2).toFixed(2);
    document.getElementById('quick_tax_sgst').value = (rate / 2).toFixed(2);
    document.getElementById('quick_tax_igst').value = rate.toFixed(2);
    const nameField = document.getElementById('quick_tax_name');
    if (!nameField.value || nameField.value.startsWith('GST ')) {
        nameField.value = 'GST ' + rate + '%';
    }
}

function showQuickToast(message) {
    let toast = document.createElement('div');
    toast.className = 'position-fixed bottom-0 end-0 p-3';
    toast.style.zIndex = '99999';
    toast.innerHTML = `
        <div class="toast align-items-center text-white bg-success border-0 show shadow-lg" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body fw-semibold">
                    <i class="fa-solid fa-circle-check me-2"></i> ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    `;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 4000);
}

// 1. AJAX Quick Create Stock Group
async function handleQuickCreateStockGroup(event) {
    event.preventDefault();
    const btn = document.getElementById('quickGroupSubmitBtn');
    const errEl = document.getElementById('quickGroupError');
    errEl.classList.add('d-none');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Saving...';

    const name = document.getElementById('quick_group_name').value;
    const parentId = document.getElementById('quick_group_parent_id').value;

    try {
        const response = await fetch("{{ route('stock-groups.store') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            },
            body: JSON.stringify({ name: name, parent_id: parentId || null })
        });

        const data = await response.json();

        if (response.ok && data.success) {
            const selectEl = document.getElementById('product_stock_group_id');
            const newOption = new Option(data.data.name, data.data.id, true, true);
            selectEl.add(newOption);
            selectEl.value = data.data.id;

            // Also add to parent group dropdown in the modal
            const parentSelect = document.getElementById('quick_group_parent_id');
            parentSelect.add(new Option(data.data.name, data.data.id));

            // Close modal & reset
            bootstrap.Modal.getInstance(document.getElementById('quickAddStockGroupModal')).hide();
            document.getElementById('quickAddStockGroupForm').reset();
            showQuickToast(`Stock Group "${data.data.name}" created and selected!`);
        } else {
            errEl.textContent = data.message || 'Failed to create Stock Group. Please check your inputs.';
            errEl.classList.remove('d-none');
        }
    } catch (err) {
        errEl.textContent = 'Server communication error. Please try again.';
        errEl.classList.remove('d-none');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-check me-1"></i> Save & Select';
    }
}

// 2. AJAX Quick Create Unit
async function handleQuickCreateUnit(event) {
    event.preventDefault();
    const btn = document.getElementById('quickUnitSubmitBtn');
    const errEl = document.getElementById('quickUnitError');
    errEl.classList.add('d-none');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Saving...';

    const name = document.getElementById('quick_unit_name').value;
    const symbol = document.getElementById('quick_unit_symbol').value;
    const decimals = document.getElementById('quick_unit_decimals').value;

    try {
        const response = await fetch("{{ route('units.store') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            },
            body: JSON.stringify({ name: name, symbol: symbol, decimal_places: decimals })
        });

        const data = await response.json();

        if (response.ok && data.success) {
            const selectEl = document.getElementById('product_unit_id');
            const newOption = new Option(data.data.display, data.data.id, true, true);
            selectEl.add(newOption);
            selectEl.value = data.data.id;

            bootstrap.Modal.getInstance(document.getElementById('quickAddUnitModal')).hide();
            document.getElementById('quickAddUnitForm').reset();
            showQuickToast(`Unit "${data.data.display}" created and selected!`);
        } else {
            errEl.textContent = data.message || 'Failed to create Unit. Please check your inputs.';
            errEl.classList.remove('d-none');
        }
    } catch (err) {
        errEl.textContent = 'Server communication error. Please try again.';
        errEl.classList.remove('d-none');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-check me-1"></i> Save & Select';
    }
}

// 3. AJAX Quick Create GST Tax Rate
async function handleQuickCreateTax(event) {
    event.preventDefault();
    const btn = document.getElementById('quickTaxSubmitBtn');
    const errEl = document.getElementById('quickTaxError');
    errEl.classList.add('d-none');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Saving...';

    const name = document.getElementById('quick_tax_name').value;
    const rate = document.getElementById('quick_tax_rate').value;
    const cess = document.getElementById('quick_tax_cess').value;
    const cgst = document.getElementById('quick_tax_cgst').value;
    const sgst = document.getElementById('quick_tax_sgst').value;
    const igst = document.getElementById('quick_tax_igst').value;

    try {
        const response = await fetch("{{ route('taxes.store') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            },
            body: JSON.stringify({
                name: name,
                rate: rate,
                cess_rate: cess,
                cgst_rate: cgst,
                sgst_rate: sgst,
                igst_rate: igst,
                is_active: 1
            })
        });

        const data = await response.json();

        if (response.ok && data.success) {
            const selectEl = document.getElementById('product_tax_master_id');
            const newOption = new Option(data.data.display, data.data.id, true, true);
            selectEl.add(newOption);
            selectEl.value = data.data.id;

            bootstrap.Modal.getInstance(document.getElementById('quickAddTaxModal')).hide();
            document.getElementById('quickAddTaxForm').reset();
            showQuickToast(`GST Rate "${data.data.display}" created and selected!`);
        } else {
            errEl.textContent = data.message || 'Failed to create Tax Rate. Please check your inputs.';
            errEl.classList.remove('d-none');
        }
    } catch (err) {
        errEl.textContent = 'Server communication error. Please try again.';
        errEl.classList.remove('d-none');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-check me-1"></i> Save & Select';
    }
}
</script>
