@extends('layouts.app')

@section('title', 'Fixed Assets & Operating Expenses')

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800 fw-bold"><i class="fa-solid fa-calculator text-primary me-2"></i>Fixed Assets & Operating Expenses</h1>
            <p class="text-muted small mb-0">Asset capitalization, SLM / WDV depreciation schedules, operating cost tracking, and departmental cost centres.</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#newCostCentreModal">
                <i class="fa-solid fa-network-wired me-1"></i> New Cost Centre
            </button>
            <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#newExpenseModal">
                <i class="fa-solid fa-receipt me-1"></i> Record Expense
            </button>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newAssetModal">
                <i class="fa-solid fa-vault me-1"></i> Register Fixed Asset
            </button>
        </div>
    </div>

    <!-- Overview Metrics -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
                <div class="text-muted small fw-semibold">Gross Asset Block</div>
                <div class="fs-4 fw-bold text-dark mt-1">₹ {{ number_format($totalAssetCost, 2) }}</div>
                <div class="small text-muted mt-1">{{ $assets->count() }} Capitalized Assets</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
                <div class="text-muted small fw-semibold">Net Book Value</div>
                <div class="fs-4 fw-bold text-success mt-1">₹ {{ number_format($totalBookValue, 2) }}</div>
                <div class="small text-muted mt-1">After SLM/WDV Depreciation</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
                <div class="text-muted small fw-semibold">Operating Expenses</div>
                <div class="fs-4 fw-bold text-danger mt-1">₹ {{ number_format($totalExpenses, 2) }}</div>
                <div class="small text-muted mt-1">{{ $expenses->count() }} Disbursed Vouchers</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
                <div class="text-muted small fw-semibold">Cost Centres</div>
                <div class="fs-4 fw-bold text-dark mt-1">{{ $costCentres->count() }} Allocated</div>
                <div class="small text-muted mt-1">Departmental P&L</div>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <ul class="nav nav-tabs mb-4" id="assetTabs" role="tablist">
        <li class="nav-item">
            <button class="nav-link active fw-semibold" id="assets-tab" data-bs-toggle="tab" data-bs-target="#assetsPane" type="button">
                <i class="fa-solid fa-vault me-1"></i> Fixed Asset Register
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link fw-semibold" id="expenses-tab" data-bs-toggle="tab" data-bs-target="#expensesPane" type="button">
                <i class="fa-solid fa-receipt me-1"></i> Operating Expenses Log
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link fw-semibold" id="costcentres-tab" data-bs-toggle="tab" data-bs-target="#costcentresPane" type="button">
                <i class="fa-solid fa-network-wired me-1"></i> Cost & Profit Centres
            </button>
        </li>
    </ul>

    <div class="tab-content" id="assetTabsContent">
        <!-- Asset Register Pane -->
        <div class="tab-pane fade show active" id="assetsPane">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Asset Code</th>
                                    <th>Asset Description</th>
                                    <th>Purchase Date</th>
                                    <th>Purchase Cost</th>
                                    <th>Method</th>
                                    <th>Rate (%)</th>
                                    <th>Accumulated Depr.</th>
                                    <th>Net Book Value</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($assets as $a)
                                    <tr>
                                        <td><span class="badge bg-secondary font-monospace">{{ $a->asset_code }}</span></td>
                                        <td>
                                            <div class="fw-bold">{{ $a->asset_name }}</div>
                                            <div class="small text-muted">{{ $a->location ?? 'Factory Depot' }} | Dep: {{ $a->department ?? 'General' }}</div>
                                        </td>
                                        <td>{{ $a->purchase_date->format('d M Y') }}</td>
                                        <td class="fw-semibold">₹ {{ number_format($a->purchase_cost, 2) }}</td>
                                        <td><span class="badge bg-dark">{{ $a->depreciation_method }}</span></td>
                                        <td>{{ $a->depreciation_rate }}%</td>
                                        <td class="text-danger">- ₹ {{ number_format($a->accumulated_depreciation, 2) }}</td>
                                        <td class="fw-bold text-success fs-6">₹ {{ number_format($a->book_value, 2) }}</td>
                                        <td><span class="badge bg-success">{{ $a->status }}</span></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-4 text-muted">No fixed assets logged yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Expenses Pane -->
        <div class="tab-pane fade" id="expensesPane">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Category</th>
                                    <th>Paid To</th>
                                    <th>Cost Centre</th>
                                    <th>Payment Mode</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($expenses as $e)
                                    <tr>
                                        <td>{{ $e->expense_date->format('d M Y') }}</td>
                                        <td><strong>{{ $e->category?->name }}</strong></td>
                                        <td>{{ $e->paid_to }}</td>
                                        <td>{{ $e->costCentre?->name ?? 'General Overhead' }}</td>
                                        <td><span class="badge bg-info text-dark">{{ $e->payment_mode }}</span></td>
                                        <td class="fw-bold text-danger">₹ {{ number_format($e->amount, 2) }}</td>
                                        <td><span class="badge bg-success">{{ $e->status }}</span></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">No expense vouchers posted yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cost Centres Pane -->
        <div class="tab-pane fade" id="costcentresPane">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Code</th>
                                    <th>Cost Centre Name</th>
                                    <th>Type</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($costCentres as $cc)
                                    <tr>
                                        <td><span class="badge bg-secondary font-monospace">{{ $cc->code ?? 'CC-'.$cc->id }}</span></td>
                                        <td><strong>{{ $cc->name }}</strong></td>
                                        <td><span class="badge bg-primary">{{ $cc->type }}</span></td>
                                        <td>{{ $cc->description ?? '—' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">No cost centres created.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Register Asset Modal -->
<div class="modal fade" id="newAssetModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form action="{{ route('assets-expenses.assets.store') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-vault text-primary me-2"></i>Capitalize Fixed Asset</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body row g-3">
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Asset Code *</label>
                    <input type="text" name="asset_code" class="form-control" required value="AST-{{ rand(100, 999) }}">
                </div>
                <div class="col-md-8">
                    <label class="form-label small fw-bold">Asset Name *</label>
                    <input type="text" name="asset_name" class="form-control" required placeholder="e.g. CNC Laser Battery Welder 5kW">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Purchase Date *</label>
                    <input type="date" name="purchase_date" class="form-control" required value="{{ date('Y-m-d') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Purchase Cost (₹) *</label>
                    <input type="number" step="0.01" name="purchase_cost" class="form-control" required placeholder="450000">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Depreciation Method *</label>
                    <select name="depreciation_method" class="form-select" required>
                        <option value="SLM">Straight Line Method (SLM)</option>
                        <option value="WDV">Written Down Value (WDV)</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Annual Rate (%) *</label>
                    <input type="number" step="0.01" name="depreciation_rate" class="form-control" required value="15">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Useful Life (Years) *</label>
                    <input type="number" name="useful_life_years" class="form-control" required value="7">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Physical Location</label>
                    <input type="text" name="location" class="form-control" placeholder="Plant 1 - Assembly Hall A">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Department</label>
                    <input type="text" name="department" class="form-control" placeholder="Manufacturing & R&D">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary fw-bold">Save Asset Record</button>
            </div>
        </form>
    </div>
</div>

<!-- Record Expense Modal -->
<div class="modal fade" id="newExpenseModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('assets-expenses.expenses.store') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title fw-bold text-danger"><i class="fa-solid fa-receipt me-2"></i>Post Operating Expense</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body row g-3">
                <div class="col-md-12">
                    <label class="form-label small fw-bold">Expense Category *</label>
                    <select name="expense_category_id" class="form-select" required>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Date *</label>
                    <input type="date" name="expense_date" class="form-control" required value="{{ date('Y-m-d') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Amount (₹) *</label>
                    <input type="number" step="0.01" name="amount" class="form-control" required placeholder="15000">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Paid To *</label>
                    <input type="text" name="paid_to" class="form-control" required placeholder="e.g. BSES Delhi (Electricity)">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Payment Mode *</label>
                    <select name="payment_mode" class="form-select" required>
                        <option value="BANK_TRANSFER" selected>Bank Transfer (NEFT/RTGS)</option>
                        <option value="CASH">Cash in Hand</option>
                        <option value="CHEQUE">Cheque</option>
                        <option value="UPI">UPI / Digital</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-danger fw-bold">Post Expense Entry</button>
            </div>
        </form>
    </div>
</div>

<!-- New Cost Centre Modal -->
<div class="modal fade" id="newCostCentreModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('assets-expenses.cost-centres.store') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Create Cost Centre</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body row g-3">
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Name *</label>
                    <input type="text" name="name" class="form-control" required placeholder="e.g. Solar EPC Division">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Type *</label>
                    <select name="type" class="form-select" required>
                        <option value="DEPARTMENT">Department</option>
                        <option value="BRANCH">Branch</option>
                        <option value="PROJECT">Project</option>
                        <option value="DIVISION">Division</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Create Centre</button>
            </div>
        </form>
    </div>
</div>
@endsection
