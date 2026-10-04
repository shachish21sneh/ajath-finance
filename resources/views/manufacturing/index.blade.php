@extends('layouts.app')

@section('title', 'Manufacturing & Bill of Materials')

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800 fw-bold"><i class="fa-solid fa-industry text-primary me-2"></i>Manufacturing & Production Management</h1>
            <p class="text-muted small mb-0">Bill of Materials (BOM), Assembly Lines, Material Requisitions, Real-time Stock Consumption, and Job Work.</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#createBomModal">
                <i class="fa-solid fa-layer-group me-1"></i> New Bill of Materials (BOM)
            </button>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newProductionOrderModal">
                <i class="fa-solid fa-play me-1"></i> Launch Production Run
            </button>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold">Standard BOMs</div>
                        <div class="fs-4 fw-bold text-dark mt-1">{{ $boms->count() }} Recipes</div>
                        <div class="small text-success mt-1"><i class="fa-solid fa-check me-1"></i>LiFePO4 & Solar Assemblies</div>
                    </div>
                    <div class="bg-primary-subtle text-primary p-3 rounded-circle fs-4">
                        <i class="fa-solid fa-sitemap"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold">Production Orders</div>
                        <div class="fs-4 fw-bold text-dark mt-1">{{ $productionOrders->count() }} Orders</div>
                        <div class="small text-muted mt-1">{{ $productionOrders->where('status', 'completed')->count() }} Completed</div>
                    </div>
                    <div class="bg-success-subtle text-success p-3 rounded-circle fs-4">
                        <i class="fa-solid fa-gears"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold">Finished Goods Built</div>
                        <div class="fs-4 fw-bold text-dark mt-1">{{ $productionOrders->sum('completed_qty') }} Units</div>
                        <div class="small text-info mt-1">QC Passed & Tagged</div>
                    </div>
                    <div class="bg-info-subtle text-info p-3 rounded-circle fs-4">
                        <i class="fa-solid fa-box-open"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold">Job Work Orders</div>
                        <div class="fs-4 fw-bold text-dark mt-1">{{ $jobWorkOrders->count() }} Active</div>
                        <div class="small text-muted mt-1">{{ $jobWorkers->count() }} Contractors</div>
                    </div>
                    <div class="bg-warning-subtle text-warning p-3 rounded-circle fs-4">
                        <i class="fa-solid fa-handshake-angle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <ul class="nav nav-tabs mb-4" id="mfgTabs" role="tablist">
        <li class="nav-item">
            <button class="nav-link active fw-semibold" id="boms-tab" data-bs-toggle="tab" data-bs-target="#bomsPane" type="button">
                <i class="fa-solid fa-list-check me-1"></i> Bill of Materials (BOM)
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link fw-semibold" id="orders-tab" data-bs-toggle="tab" data-bs-target="#ordersPane" type="button">
                <i class="fa-solid fa-hammer me-1"></i> Production Orders & Execution
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link fw-semibold" id="jobwork-tab" data-bs-toggle="tab" data-bs-target="#jobworkPane" type="button">
                <i class="fa-solid fa-people-carry-box me-1"></i> Job Work Management
            </button>
        </li>
    </ul>

    <div class="tab-content" id="mfgTabsContent">
        <!-- BOMs Pane -->
        <div class="tab-pane fade show active" id="bomsPane">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Code</th>
                                    <th>Finished Product Output</th>
                                    <th>Components / Raw Materials</th>
                                    <th>Labor Cost</th>
                                    <th>Unit Mfg Cost</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($boms as $bom)
                                    <tr>
                                        <td><span class="badge bg-secondary font-monospace">{{ $bom->bom_code }}</span></td>
                                        <td>
                                            <div class="fw-bold">{{ $bom->bom_name }}</div>
                                            <div class="small text-muted">Output: {{ $bom->output_qty }} Unit(s)</div>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary rounded-pill">{{ $bom->items->count() }} Materials</span>
                                            <div class="small text-muted mt-1">
                                                @foreach($bom->items->take(2) as $it)
                                                    {{ $it->rawMaterial?->name }} ({{ $it->quantity }}), 
                                                @endforeach
                                                @if($bom->items->count() > 2) ... @endif
                                            </div>
                                        </td>
                                        <td>₹ {{ number_format($bom->labor_cost, 2) }}</td>
                                        <td class="text-primary fw-bold">₹ {{ number_format($bom->total_unit_cost, 2) }}</td>
                                        <td><span class="badge bg-success">ACTIVE</span></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">No BOM recipes defined yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Production Orders Pane -->
        <div class="tab-pane fade" id="ordersPane">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Order #</th>
                                    <th>Date</th>
                                    <th>Finished Goods to Build</th>
                                    <th>Planned Qty</th>
                                    <th>Total Planned Cost</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($productionOrders as $po)
                                    <tr>
                                        <td><span class="badge bg-dark font-monospace">{{ $po->order_no }}</span></td>
                                        <td>{{ $po->order_date->format('d M Y') }}</td>
                                        <td><strong>{{ $po->bom?->bom_name }}</strong></td>
                                        <td>{{ $po->planned_qty }} Units</td>
                                        <td class="fw-semibold">₹ {{ number_format($po->total_cost, 2) }}</td>
                                        <td>
                                            <span class="badge bg-{{ $po->status === 'completed' ? 'success' : 'warning text-dark' }}">
                                                {{ strtoupper($po->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($po->status !== 'completed')
                                                <form action="{{ route('manufacturing.orders.complete', $po->id) }}" method="POST" onsubmit="return confirm('Execute production run? This will atomically consume raw materials from stock and increment finished goods inventory.');">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success fw-bold">
                                                        <i class="fa-solid fa-play me-1"></i> Execute & Post Stock
                                                    </button>
                                                </form>
                                            @else
                                                <span class="text-success small fw-bold"><i class="fa-solid fa-check-double me-1"></i>Produced & Stored</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">No production orders launched yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Job Work Pane -->
        <div class="tab-pane fade" id="jobworkPane">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold">Contractor Job Work Orders</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Order #</th>
                                    <th>Job Worker / Contractor</th>
                                    <th>Order Date</th>
                                    <th>Expected Return</th>
                                    <th>Job Work Charges</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($jobWorkOrders as $jwo)
                                    <tr>
                                        <td><span class="badge bg-secondary font-monospace">{{ $jwo->order_no }}</span></td>
                                        <td><strong>{{ $jwo->jobWorker?->name }}</strong></td>
                                        <td>{{ $jwo->order_date->format('d M Y') }}</td>
                                        <td>{{ $jwo->expected_return_date ? $jwo->expected_return_date->format('d M Y') : '—' }}</td>
                                        <td class="fw-semibold">₹ {{ number_format($jwo->charges, 2) }}</td>
                                        <td><span class="badge bg-info text-dark">{{ strtoupper($jwo->status) }}</span></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">No external job work orders active.</td>
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

<!-- Launch Production Order Modal -->
<div class="modal fade" id="newProductionOrderModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('manufacturing.orders.store') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-play text-primary me-2"></i>Launch Production Run</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body row g-3">
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Order Number *</label>
                    <input type="text" name="order_no" class="form-control" required value="PROD-{{ date('Ymd') }}-{{ rand(10, 99) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Order Date *</label>
                    <input type="date" name="order_date" class="form-control" required value="{{ date('Y-m-d') }}">
                </div>
                <div class="col-md-12">
                    <label class="form-label small fw-bold">Select Bill of Materials (BOM) *</label>
                    <select name="bom_id" class="form-select" required>
                        @foreach($boms as $b)
                            <option value="{{ $b->id }}">{{ $b->bom_name }} (Cost/unit: ₹{{ number_format($b->total_unit_cost, 2) }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Warehouse / Depot *</label>
                    <select name="warehouse_id" class="form-select" required>
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Planned Quantity *</label>
                    <input type="number" step="1" name="planned_qty" class="form-control" required value="5" min="1">
                </div>
                <div class="col-md-12">
                    <label class="form-label small fw-bold">Notes / Production Batch Instructions</label>
                    <input type="text" name="remarks" class="form-control" placeholder="e.g. Standard cell balancing and cycle QC">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary fw-bold">Schedule Production Order</button>
            </div>
        </form>
    </div>
</div>
@endsection
