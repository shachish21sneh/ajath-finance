@extends('layouts.app')

@section('title', 'Solar ERP & Project Management')

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800 fw-bold"><i class="fa-solid fa-solar-panel text-warning me-2"></i>Solar ERP & EPC Project Suite</h1>
            <p class="text-muted small mb-0">Rooftop site surveys, automated sizing calculator, MNRE subsidies, net-metering compliance, and AMC lifecycle.</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-warning text-dark" data-bs-toggle="modal" data-bs-target="#newSurveyModal">
                <i class="fa-solid fa-compass-drafting me-1"></i> Log Site Survey
            </button>
            <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#newQuotationModal">
                <i class="fa-solid fa-calculator me-1"></i> Sizing & Quotation
            </button>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newProjectModal">
                <i class="fa-solid fa-plus me-1"></i> Commission Project
            </button>
        </div>
    </div>

    <!-- Quick Sizing Calculator Alert Box -->
    <div class="card border-0 shadow-sm rounded-3 mb-4 bg-primary text-white p-3">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h5 class="fw-bold mb-1"><i class="fa-solid fa-sun me-2 text-warning"></i>Integrated PM Surya Ghar & MNRE Subsidy Engine</h5>
                <p class="small mb-0 opacity-75">Automated rooftop shadow analysis, solar generation units estimation (120 units/kW/month), and grid net-metering tracker.</p>
            </div>
            <div class="col-md-4 text-md-end">
                <button class="btn btn-light text-primary fw-bold" data-bs-toggle="modal" data-bs-target="#newQuotationModal">
                    <i class="fa-solid fa-bolt me-1"></i> Generate Sizing Proposal
                </button>
            </div>
        </div>
    </div>

    <!-- Metrics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold">Total Installations</div>
                        <div class="fs-4 fw-bold text-dark mt-1">{{ $projects->count() }} Projects</div>
                        <div class="small text-success mt-1">{{ $projects->where('installation_status', 'COMMISSIONED')->count() }} Commissioned</div>
                    </div>
                    <div class="bg-warning-subtle text-warning p-3 rounded-circle fs-4">
                        <i class="fa-solid fa-solar-panel"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold">Capacity Built</div>
                        <div class="fs-4 fw-bold text-dark mt-1">{{ $projects->sum('capacity_kw') }} kW</div>
                        <div class="small text-muted mt-1">Solar PV Generation</div>
                    </div>
                    <div class="bg-success-subtle text-success p-3 rounded-circle fs-4">
                        <i class="fa-solid fa-bolt"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold">Site Surveys</div>
                        <div class="fs-4 fw-bold text-dark mt-1">{{ $surveys->count() }} Completed</div>
                        <div class="small text-info mt-1">Shadow-free Audits</div>
                    </div>
                    <div class="bg-info-subtle text-info p-3 rounded-circle fs-4">
                        <i class="fa-solid fa-ruler-combined"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold">Active AMCs</div>
                        <div class="fs-4 fw-bold text-dark mt-1">{{ $amcs->where('status', 'ACTIVE')->count() }} Contracts</div>
                        <div class="small text-muted mt-1">Preventive Service</div>
                    </div>
                    <div class="bg-primary-subtle text-primary p-3 rounded-circle fs-4">
                        <i class="fa-solid fa-handshake"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <ul class="nav nav-tabs mb-4" id="solarTabs" role="tablist">
        <li class="nav-item">
            <button class="nav-link active fw-semibold" id="projects-tab" data-bs-toggle="tab" data-bs-target="#projectsPane" type="button">
                <i class="fa-solid fa-diagram-project me-1"></i> Solar EPC Projects
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link fw-semibold" id="surveys-tab" data-bs-toggle="tab" data-bs-target="#surveysPane" type="button">
                <i class="fa-solid fa-house-chimney-crack me-1"></i> Rooftop Site Surveys
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link fw-semibold" id="quotes-tab" data-bs-toggle="tab" data-bs-target="#quotesPane" type="button">
                <i class="fa-solid fa-file-signature me-1"></i> Quotations & Subsidies
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link fw-semibold" id="amc-tab" data-bs-toggle="tab" data-bs-target="#amcPane" type="button">
                <i class="fa-solid fa-shield-halved me-1"></i> Annual Maintenance (AMC)
            </button>
        </li>
    </ul>

    <div class="tab-content" id="solarTabsContent">
        <!-- Projects Pane -->
        <div class="tab-pane fade show active" id="projectsPane">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Project Code</th>
                                    <th>Customer & Site</th>
                                    <th>Capacity</th>
                                    <th>Project Value</th>
                                    <th>Net-Metering Status</th>
                                    <th>Subsidy Status</th>
                                    <th>Installation Stage</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($projects as $p)
                                    <tr>
                                        <td><span class="badge bg-secondary font-monospace">{{ $p->project_code }}</span></td>
                                        <td>
                                            <div class="fw-bold">{{ $p->customer?->name }}</div>
                                            <div class="small text-muted">{{ Str::limit($p->site_address, 35) }}</div>
                                        </td>
                                        <td><span class="badge bg-warning text-dark fs-6">{{ $p->capacity_kw }} kW</span></td>
                                        <td class="fw-bold">₹ {{ number_format($p->total_project_cost, 2) }}</td>
                                        <td><span class="badge bg-info text-dark">{{ $p->net_metering_status }}</span></td>
                                        <td><span class="badge bg-success">{{ $p->subsidy_status }}</span></td>
                                        <td>
                                            <span class="badge bg-{{ $p->installation_status === 'COMMISSIONED' ? 'success' : 'primary' }}">
                                                {{ $p->installation_status }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">No solar projects registered.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Site Surveys Pane -->
        <div class="tab-pane fade" id="surveysPane">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Site / Client</th>
                                    <th>Roof Type</th>
                                    <th>Shadow-Free Area</th>
                                    <th>Sanctioned Load</th>
                                    <th>Monthly Units</th>
                                    <th>Recommended Capacity</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($surveys as $s)
                                    <tr>
                                        <td>{{ $s->survey_date->format('d M Y') }}</td>
                                        <td><strong>{{ $s->lead?->customer_name ?? ($s->customer?->name ?? 'Direct Site') }}</strong></td>
                                        <td><span class="badge bg-dark">{{ $s->roof_type }}</span></td>
                                        <td>{{ $s->shadow_free_area_sqft }} sq ft</td>
                                        <td>{{ $s->sanctioned_load_kw }} kW ({{ $s->phase }})</td>
                                        <td>{{ $s->monthly_consumption_kwh }} kWh</td>
                                        <td><span class="badge bg-success fs-6">{{ $s->recommended_capacity_kw }} kW</span></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">No site surveys logged yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quotations Pane -->
        <div class="tab-pane fade" id="quotesPane">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Quote #</th>
                                    <th>Customer</th>
                                    <th>System Size</th>
                                    <th>Panels & Inverter</th>
                                    <th>System Cost</th>
                                    <th>MNRE Subsidy</th>
                                    <th>Net Customer Payable</th>
                                    <th>Est. Payback</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($quotations as $q)
                                    <tr>
                                        <td><span class="badge bg-secondary font-monospace">{{ $q->quotation_no }}</span></td>
                                        <td><strong>{{ $q->lead?->customer_name ?? ($q->customer?->name ?? 'Client') }}</strong></td>
                                        <td><span class="badge bg-warning text-dark">{{ $q->system_capacity_kw }} kW</span></td>
                                        <td><small>{{ $q->panel_qty }}x {{ $q->panel_model }}</small></td>
                                        <td>₹ {{ number_format($q->system_cost, 2) }}</td>
                                        <td class="text-success fw-bold">- ₹ {{ number_format($q->subsidy_amount, 2) }}</td>
                                        <td class="text-primary fw-bold fs-6">₹ {{ number_format($q->net_payable, 2) }}</td>
                                        <td>{{ $q->payback_years }} Years</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4 text-muted">No solar quotations created yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- AMC Pane -->
        <div class="tab-pane fade" id="amcPane">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>AMC Code</th>
                                    <th>Solar Project</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Annual Fee</th>
                                    <th>Scheduled Visits</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($amcs as $a)
                                    <tr>
                                        <td><span class="badge bg-dark font-monospace">{{ $a->amc_code }}</span></td>
                                        <td><strong>{{ $a->project?->project_name }}</strong></td>
                                        <td>{{ $a->start_date->format('d M Y') }}</td>
                                        <td>{{ $a->end_date->format('d M Y') }}</td>
                                        <td class="fw-semibold">₹ {{ number_format($a->annual_fee, 2) }}</td>
                                        <td>{{ $a->visits_completed }} / {{ $a->visits_per_year }} Visits</td>
                                        <td><span class="badge bg-success">{{ $a->status }}</span></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">No active AMC contracts found.</td>
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

<!-- Log Site Survey Modal -->
<div class="modal fade" id="newSurveyModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form action="{{ route('solar.surveys.store') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-compass-drafting text-warning me-2"></i>Rooftop Technical Site Survey</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body row g-3">
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Customer Account *</label>
                    <select name="customer_ledger_id" class="form-select" required>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->city ?? 'Delhi' }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Survey Date *</label>
                    <input type="date" name="survey_date" class="form-control" required value="{{ date('Y-m-d') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Surveyor Engineer Name *</label>
                    <input type="text" name="surveyor_name" class="form-control" required value="Amit Sharma (Site Engineer)">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Roof Structure Type *</label>
                    <select name="roof_type" class="form-select" required>
                        <option value="RCC Flat">RCC Flat Roof</option>
                        <option value="Metal Sheet">Industrial Metal Sheet Roof</option>
                        <option value="Sloped Tile">Sloped Tile Roof</option>
                        <option value="Ground Mounted">Ground Mounted Area</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Total Roof Area (sq ft) *</label>
                    <input type="number" step="0.1" name="roof_area_sqft" class="form-control" required value="1200">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Shadow-Free Area (sq ft) *</label>
                    <input type="number" step="0.1" name="shadow_free_area_sqft" class="form-control" required value="850">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Tilt Angle (Degrees)</label>
                    <input type="number" step="0.1" name="tilt_angle" class="form-control" required value="28">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Sanctioned Grid Load (kW) *</label>
                    <input type="number" step="0.1" name="sanctioned_load_kw" class="form-control" required value="10">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Monthly Consumption (kWh) *</label>
                    <input type="number" step="1" name="monthly_consumption_kwh" class="form-control" required value="950">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Electrical Phase</label>
                    <select name="phase" class="form-select">
                        <option value="Three Phase">Three Phase (415V)</option>
                        <option value="Single Phase">Single Phase (230V)</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Electricity Consumer CA Number</label>
                    <input type="text" name="consumer_number" class="form-control" placeholder="e.g. 10029482910">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">DISCOM Name</label>
                    <input type="text" name="discom_name" class="form-control" value="BSES Rajdhani Power Limited">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-warning text-dark fw-bold">Save Survey & Calculate Sizing</button>
            </div>
        </form>
    </div>
</div>

<!-- Sizing & Quotation Modal -->
<div class="modal fade" id="newQuotationModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form action="{{ route('solar.quotations.store') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-calculator text-primary me-2"></i>Generate Solar System Sizing Proposal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body row g-3">
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Customer Account *</label>
                    <select name="customer_ledger_id" class="form-select" required>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Quotation Reference # *</label>
                    <input type="text" name="quotation_no" class="form-control" required value="SQ-{{ date('Ymd') }}-{{ rand(10, 99) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold">System Capacity (kW) *</label>
                    <input type="number" step="0.5" name="system_capacity_kw" class="form-control" required value="5.5" min="1">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Solar Panel Specification</label>
                    <input type="text" name="panel_model" class="form-control" value="Fuzurra 550W Mono PERC TopCon">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Solar Inverter Specification</label>
                    <input type="text" name="inverter_model" class="form-control" value="Durasol DSH-3370 Hybrid 5kW">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Quotation Date *</label>
                    <input type="date" name="quotation_date" class="form-control" required value="{{ date('Y-m-d') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Gross System Cost (Excl Tax) ₹ *</label>
                    <input type="number" step="1" name="system_cost" class="form-control" required value="285000">
                    <div class="form-text small">MNRE/PM Surya Ghar subsidy will be automatically subtracted.</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary fw-bold">Compute Subsidy & Create Quotation</button>
            </div>
        </form>
    </div>
</div>
@endsection
