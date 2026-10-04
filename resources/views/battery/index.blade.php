@extends('layouts.app')

@section('title', 'Battery ERP & Traceability Management')

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800 fw-bold"><i class="fa-solid fa-car-battery text-danger me-2"></i>Battery ERP & Lifecycle Traceability</h1>
            <p class="text-muted small mb-0">Lithium-Ion, LiFePO4, Tubular & Lead Acid: Cell batch tracking, laboratory QC certificates, and warranty claims.</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#newBatteryTestModal">
                <i class="fa-solid fa-flask-vial me-1"></i> Log Laboratory QC Test
            </button>
            <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#newWarrantyModal">
                <i class="fa-solid fa-shield-halved me-1"></i> Register Warranty
            </button>
            <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#newBatterySerialModal">
                <i class="fa-solid fa-barcode me-1"></i> Register Battery Serial
            </button>
        </div>
    </div>

    <!-- Spotlight Serial Number Search -->
    <div class="card border-0 shadow-sm rounded-3 mb-4 bg-dark text-white p-3">
        <form action="{{ route('battery.index') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-md-9">
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-secondary text-white"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="text" name="serial" class="form-control bg-secondary text-white border-secondary" placeholder="Enter Battery Serial Number (e.g. FZ-512100-2026-0001) for instant end-to-end audit trail..." value="{{ $searchQuery }}">
                </div>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-danger w-100 fw-bold"><i class="fa-solid fa-bolt me-1"></i>Trace Battery Journey</button>
            </div>
        </form>
    </div>

    @if($searchedSerial)
        <!-- Serial Lifecycle Audit Trail Card -->
        <div class="card border-danger shadow-sm rounded-3 mb-4">
            <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold"><i class="fa-solid fa-route me-2"></i>Full Traceability Timeline: {{ $searchedSerial->serial_number }}</h5>
                <span class="badge bg-light text-dark fs-6">{{ $searchedSerial->model?->product?->name }}</span>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-3 border-end">
                        <h6 class="fw-bold text-muted text-uppercase small">1. Production & Specs</h6>
                        <ul class="list-unstyled small mb-0">
                            <li><strong>Chemistry:</strong> {{ $searchedSerial->model?->chemistry }}</li>
                            <li><strong>Voltage:</strong> {{ $searchedSerial->model?->nominal_voltage }} V</li>
                            <li><strong>Capacity:</strong> {{ $searchedSerial->model?->capacity_ah }} Ah</li>
                            <li><strong>Cell Batch:</strong> {{ $searchedSerial->cell_batch_number ?? 'CATL-2026-B4' }}</li>
                            <li><strong>Mfg Date:</strong> {{ $searchedSerial->mfg_date->format('d M Y') }}</li>
                        </ul>
                    </div>
                    <div class="col-md-3 border-end">
                        <h6 class="fw-bold text-muted text-uppercase small">2. QC Test Certification</h6>
                        @if($searchedSerial->tests->isNotEmpty())
                            @php $test = $searchedSerial->tests->first(); @endphp
                            <ul class="list-unstyled small mb-0">
                                <li><strong>Cert #:</strong> <span class="badge bg-success font-monospace">{{ $test->certificate_no }}</span></li>
                                <li><strong>Pack Voltage:</strong> {{ $test->pack_voltage }} V</li>
                                <li><strong>Internal Res:</strong> {{ $test->internal_resistance_mohm }} mΩ</li>
                                <li><strong>Actual Capacity:</strong> {{ $test->actual_capacity_ah }} Ah</li>
                                <li><strong>QC Result:</strong> <span class="text-success fw-bold">{{ $test->qc_result }}</span></li>
                            </ul>
                        @else
                            <p class="small text-muted mb-0">No laboratory test certificate logged yet.</p>
                        @endif
                    </div>
                    <div class="col-md-3 border-end">
                        <h6 class="fw-bold text-muted text-uppercase small">3. Inventory & Location</h6>
                        <ul class="list-unstyled small mb-0">
                            <li><strong>Current Status:</strong> <span class="badge bg-info text-dark">{{ $searchedSerial->current_status }}</span></li>
                            <li><strong>Warehouse:</strong> {{ $searchedSerial->warehouse?->name ?? 'Head Office Depot' }}</li>
                            <li><strong>Customer:</strong> {{ $searchedSerial->customer?->name ?? 'In Distribution Pipeline' }}</li>
                            <li><strong>Dispatch Date:</strong> {{ $searchedSerial->dispatch_date ? $searchedSerial->dispatch_date->format('d M Y') : 'Pending Dispatch' }}</li>
                        </ul>
                    </div>
                    <div class="col-md-3">
                        <h6 class="fw-bold text-muted text-uppercase small">4. Warranty & Support</h6>
                        @if($searchedSerial->warranty)
                            <ul class="list-unstyled small mb-0">
                                <li><strong>Status:</strong> <span class="badge bg-success">ACTIVE WARRANTY</span></li>
                                <li><strong>Valid Until:</strong> {{ $searchedSerial->warranty->warranty_end_date->format('d M Y') }}</li>
                                <li><strong>Claims Filed:</strong> {{ $searchedSerial->warranty->claims->count() }}</li>
                            </ul>
                        @else
                            <p class="small text-muted mb-0">Warranty registration pending sale completion.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Metrics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold">Battery Models</div>
                        <div class="fs-4 fw-bold text-dark mt-1">{{ $models->count() }} Specifications</div>
                        <div class="small text-muted mt-1">12V to 51.2V LiFePO4</div>
                    </div>
                    <div class="bg-primary-subtle text-primary p-3 rounded-circle fs-4">
                        <i class="fa-solid fa-shapes"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold">Serialized Units</div>
                        <div class="fs-4 fw-bold text-dark mt-1">{{ $serials->count() }} Barcodes</div>
                        <div class="small text-success mt-1">{{ $serials->where('current_status', 'IN_STOCK')->count() }} In Stock</div>
                    </div>
                    <div class="bg-danger-subtle text-danger p-3 rounded-circle fs-4">
                        <i class="fa-solid fa-barcode"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold">Active Warranties</div>
                        <div class="fs-4 fw-bold text-dark mt-1">{{ $warranties->where('is_active', true)->count() }} Registered</div>
                        <div class="small text-info mt-1">60-Month Coverage</div>
                    </div>
                    <div class="bg-success-subtle text-success p-3 rounded-circle fs-4">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold">Warranty Claims</div>
                        <div class="fs-4 fw-bold text-dark mt-1">{{ $claims->count() }} Cases</div>
                        <div class="small text-warning mt-1">{{ $claims->where('status', 'PENDING')->count() }} Pending Review</div>
                    </div>
                    <div class="bg-warning-subtle text-warning p-3 rounded-circle fs-4">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <ul class="nav nav-tabs mb-4" id="batteryTabs" role="tablist">
        <li class="nav-item">
            <button class="nav-link active fw-semibold" id="serials-tab" data-bs-toggle="tab" data-bs-target="#serialsPane" type="button">
                <i class="fa-solid fa-list-ol me-1"></i> Serialized Inventory
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link fw-semibold" id="models-tab" data-bs-toggle="tab" data-bs-target="#modelsPane" type="button">
                <i class="fa-solid fa-battery-full me-1"></i> Battery Models
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link fw-semibold" id="qc-tab" data-bs-toggle="tab" data-bs-target="#qcPane" type="button">
                <i class="fa-solid fa-certificate me-1"></i> QC Test Certificates
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link fw-semibold" id="warranties-tab" data-bs-toggle="tab" data-bs-target="#warrantiesPane" type="button">
                <i class="fa-solid fa-shield-virus me-1"></i> Warranty & Claims
            </button>
        </li>
    </ul>

    <div class="tab-content" id="batteryTabsContent">
        <!-- Serials Pane -->
        <div class="tab-pane fade show active" id="serialsPane">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Serial Number</th>
                                    <th>Model & Description</th>
                                    <th>Cell Batch</th>
                                    <th>Mfg Date</th>
                                    <th>QC Status</th>
                                    <th>Current Lifecycle Status</th>
                                    <th>Customer / Assigned</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($serials as $s)
                                    <tr>
                                        <td>
                                            <a href="{{ route('battery.index', ['serial' => $s->serial_number]) }}" class="fw-bold text-danger text-decoration-none">
                                                <i class="fa-solid fa-barcode me-1"></i>{{ $s->serial_number }}
                                            </a>
                                        </td>
                                        <td><strong>{{ $s->model?->product?->name }}</strong></td>
                                        <td><span class="badge bg-secondary font-monospace">{{ $s->cell_batch_number ?? 'STD-BATCH' }}</span></td>
                                        <td>{{ $s->mfg_date->format('d M Y') }}</td>
                                        <td><span class="badge bg-{{ $s->qc_status === 'PASSED' ? 'success' : 'danger' }}">{{ $s->qc_status }}</span></td>
                                        <td><span class="badge bg-info text-dark">{{ $s->current_status }}</span></td>
                                        <td>{{ $s->customer?->name ?? ($s->warehouse?->name ?? 'Factory Warehouse') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">No battery serials registered.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Models Pane -->
        <div class="tab-pane fade" id="modelsPane">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Model Name</th>
                                    <th>Chemistry</th>
                                    <th>Nominal Voltage</th>
                                    <th>Capacity</th>
                                    <th>Energy Rating</th>
                                    <th>BMS Specs</th>
                                    <th>Warranty (Months)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($models as $m)
                                    <tr>
                                        <td><strong>{{ $m->product?->name }}</strong></td>
                                        <td><span class="badge bg-dark">{{ $m->chemistry }}</span></td>
                                        <td>{{ $m->nominal_voltage }} V</td>
                                        <td>{{ $m->capacity_ah }} Ah</td>
                                        <td class="text-primary fw-bold">{{ $m->energy_wh }} Wh</td>
                                        <td>{{ $m->bms_model ?? 'Smart CAN/RS485 BMS' }}</td>
                                        <td>{{ $m->warranty_months }} Months ({{ $m->free_replacement_months }} Free)</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">No battery models defined.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- QC Test Pane -->
        <div class="tab-pane fade" id="qcPane">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Certificate #</th>
                                    <th>Battery Serial</th>
                                    <th>Date</th>
                                    <th>Pack Voltage</th>
                                    <th>IR (mΩ)</th>
                                    <th>Actual Capacity</th>
                                    <th>QC Status</th>
                                    <th>Inspector</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($tests as $t)
                                    <tr>
                                        <td><span class="badge bg-dark font-monospace">{{ $t->certificate_no }}</span></td>
                                        <td><strong>{{ $t->batterySerial?->serial_number }}</strong></td>
                                        <td>{{ $t->test_date->format('d M Y') }}</td>
                                        <td>{{ $t->pack_voltage }} V</td>
                                        <td>{{ $t->internal_resistance_mohm }} mΩ</td>
                                        <td>{{ $t->actual_capacity_ah }} Ah</td>
                                        <td><span class="badge bg-{{ $t->qc_result === 'PASS' ? 'success' : 'danger' }}">{{ $t->qc_result }}</span></td>
                                        <td>{{ $t->technician_name }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4 text-muted">No laboratory test certificates logged.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Warranties Pane -->
        <div class="tab-pane fade" id="warrantiesPane">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Battery Serial</th>
                                    <th>Customer / Owner</th>
                                    <th>Purchase Date</th>
                                    <th>Warranty Expiry Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($warranties as $w)
                                    <tr>
                                        <td><strong>{{ $w->batterySerial?->serial_number }}</strong></td>
                                        <td>{{ $w->customer?->name ?? 'Retail Purchaser' }}</td>
                                        <td>{{ $w->purchase_date->format('d M Y') }}</td>
                                        <td>
                                            <strong>{{ $w->warranty_end_date->format('d M Y') }}</strong>
                                            @if($w->warranty_end_date->isPast())
                                                <span class="badge bg-danger ms-1">EXPIRED</span>
                                            @else
                                                <span class="badge bg-success ms-1">ACTIVE</span>
                                            @endif
                                        </td>
                                        <td>{{ $w->warranty_type }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">No registered battery warranties found.</td>
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

<!-- Register Battery Serial Modal -->
<div class="modal fade" id="newBatterySerialModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('battery.serials.store') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-barcode text-danger me-2"></i>Register Serialized Battery Pack</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body row g-3">
                <div class="col-md-12">
                    <label class="form-label small fw-bold">Battery Model *</label>
                    <select name="battery_model_id" class="form-select" required>
                        @foreach($models as $m)
                            <option value="{{ $m->id }}">{{ $m->product?->name }} ({{ $m->nominal_voltage }}V {{ $m->capacity_ah }}Ah)</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Serial Number *</label>
                    <input type="text" name="serial_number" class="form-control" required value="FZ-{{ date('Ymd') }}-{{ rand(1000, 9999) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Cell Lot / Batch *</label>
                    <input type="text" name="cell_batch_number" class="form-control" required value="CELL-BATCH-{{ date('Ym') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Warehouse Depot *</label>
                    <select name="warehouse_id" class="form-select" required>
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Manufacturing Date *</label>
                    <input type="date" name="mfg_date" class="form-control" required value="{{ date('Y-m-d') }}">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-danger fw-bold">Register & Print Barcode</button>
            </div>
        </form>
    </div>
</div>
@endsection
