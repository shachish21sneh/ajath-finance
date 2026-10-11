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
            <button class="btn btn-outline-info" data-bs-toggle="modal" data-bs-target="#newLeadModal">
                <i class="fa-solid fa-user-plus me-1"></i> New Lead
            </button>
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
                        <div class="fs-4 fw-bold text-dark mt-1">{{ $totalProjectsCount }} Projects</div>
                        <div class="small text-success mt-1">{{ $commissionedProjectsCount }} Commissioned</div>
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
                        <div class="fs-4 fw-bold text-dark mt-1">{{ number_format($totalCapacityKw, 1) }} kW</div>
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
                        <div class="fs-4 fw-bold text-dark mt-1">{{ $totalSurveysCount }} Completed</div>
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
                        <div class="fs-4 fw-bold text-dark mt-1">{{ $activeAmcsCount }} Contracts</div>
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
            <button class="nav-link fw-semibold" id="leads-tab" data-bs-toggle="tab" data-bs-target="#leadsPane" type="button">
                <i class="fa-solid fa-filter-circle-dollar me-1"></i> Leads & Inquiries
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
                                    <th class="text-end pe-3">Action</th>
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
                                        <td class="text-end pe-3">
                                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editProjectModal{{ $p->id }}" title="Edit Project">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </button>
                                        </td>
                                    </tr>

                                    <!-- Edit Project Modal -->
                                    <div class="modal fade" id="editProjectModal{{ $p->id }}" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <form action="{{ route('solar.projects.update', $p->id) }}" method="POST" class="modal-content">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-header">
                                                    <h5 class="modal-title fw-bold"><i class="fa-solid fa-pen-to-square me-2 text-primary"></i>Edit Solar Project ({{ $p->project_code }})</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body row g-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label small fw-bold">Customer Account *</label>
                                                        <select name="customer_ledger_id" class="form-select" required>
                                                            @foreach($customers as $c)
                                                                <option value="{{ $c->id }}" {{ $p->customer_ledger_id == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label small fw-bold">Project Name *</label>
                                                        <input type="text" name="project_name" class="form-control" required value="{{ $p->project_name }}">
                                                    </div>
                                                    <div class="col-12">
                                                        <label class="form-label small fw-bold">Site Installation Address *</label>
                                                        <textarea name="site_address" class="form-control" rows="2" required>{{ $p->site_address }}</textarea>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label small fw-bold">Capacity (kW) *</label>
                                                        <input type="number" step="0.1" name="capacity_kw" class="form-control" required value="{{ $p->capacity_kw }}" min="1">
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label small fw-bold">Total Project Cost ₹ *</label>
                                                        <input type="number" step="0.01" name="total_project_cost" class="form-control" required value="{{ $p->total_project_cost }}">
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label small fw-bold">Installation Stage *</label>
                                                        <select name="installation_status" class="form-select" required>
                                                            @foreach(['NOT_STARTED', 'MATERIAL_DISPATCHED', 'STRUCTURE_COMPLETED', 'WIRING_COMPLETED', 'COMMISSIONED'] as $stage)
                                                                <option value="{{ $stage }}" {{ $p->installation_status == $stage ? 'selected' : '' }}>{{ $stage }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label small fw-bold">Net Metering Status *</label>
                                                        <select name="net_metering_status" class="form-select" required>
                                                            @foreach(['APPLIED', 'FEASIBILITY_APPROVED', 'METER_INSTALLED', 'COMMISSIONED'] as $nms)
                                                                <option value="{{ $nms }}" {{ $p->net_metering_status == $nms ? 'selected' : '' }}>{{ $nms }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label small fw-bold">Subsidy Status *</label>
                                                        <select name="subsidy_status" class="form-select" required>
                                                            @foreach(['NOT_APPLICABLE', 'APPLIED', 'APPROVED', 'DISBURSED'] as $subs)
                                                                <option value="{{ $subs }}" {{ $p->subsidy_status == $subs ? 'selected' : '' }}>{{ $subs }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label small fw-bold">Commissioning Date</label>
                                                        <input type="date" name="commissioning_date" class="form-control" value="{{ $p->commissioning_date ? date('Y-m-d', strtotime($p->commissioning_date)) : '' }}">
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-primary fw-bold">Save Changes</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4 text-muted">No solar projects registered.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($projects->hasPages())
                    <div class="card-footer bg-white border-top py-3">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <span class="small text-muted">Showing {{ $projects->firstItem() }} to {{ $projects->lastItem() }} of {{ $projects->total() }} projects</span>
                            {{ $projects->appends(request()->except('projects_page'))->links() }}
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Leads Pane -->
        <div class="tab-pane fade" id="leadsPane">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Customer / Prospect</th>
                                    <th>Contact</th>
                                    <th>Location</th>
                                    <th>Required Capacity</th>
                                    <th>Source</th>
                                    <th>Status</th>
                                    <th class="text-end pe-3">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($leads as $l)
                                    <tr>
                                        <td><div class="fw-bold">{{ $l->customer_name }}</div></td>
                                        <td>
                                            <div><i class="fa-solid fa-phone me-1 small text-muted"></i>{{ $l->phone }}</div>
                                            @if($l->email)<div class="small text-muted">{{ $l->email }}</div>@endif
                                        </td>
                                        <td>{{ $l->city ? $l->city . ', ' . $l->state : 'N/A' }}</td>
                                        <td><span class="badge bg-warning text-dark fs-6">{{ $l->estimated_kw }} kW</span></td>
                                        <td><span class="badge bg-secondary">{{ $l->source ?? 'Direct' }}</span></td>
                                        <td><span class="badge bg-primary">{{ $l->status }}</span></td>
                                        <td class="text-end pe-3">
                                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editLeadModal{{ $l->id }}" title="Edit Lead">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </button>
                                        </td>
                                    </tr>

                                    <!-- Edit Lead Modal -->
                                    <div class="modal fade" id="editLeadModal{{ $l->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <form action="{{ route('solar.leads.update', $l->id) }}" method="POST" class="modal-content">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-header">
                                                    <h5 class="modal-title fw-bold"><i class="fa-solid fa-pen-to-square me-2 text-primary"></i>Edit Solar Lead</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body row g-3">
                                                    <div class="col-12">
                                                        <label class="form-label small fw-bold">Customer Name *</label>
                                                        <input type="text" name="customer_name" class="form-control" required value="{{ $l->customer_name }}">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label small fw-bold">Phone *</label>
                                                        <input type="text" name="phone" class="form-control" required value="{{ $l->phone }}">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label small fw-bold">Email</label>
                                                        <input type="email" name="email" class="form-control" value="{{ $l->email }}">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label small fw-bold">City</label>
                                                        <input type="text" name="city" class="form-control" value="{{ $l->city }}">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label small fw-bold">Estimated kW *</label>
                                                        <input type="number" step="0.5" name="estimated_kw" class="form-control" required value="{{ $l->estimated_kw }}" min="1">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label small fw-bold">Lead Source</label>
                                                        <input type="text" name="source" class="form-control" value="{{ $l->source }}">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label small fw-bold">Status *</label>
                                                        <select name="status" class="form-select" required>
                                                            @foreach(['NEW', 'CONTACTED', 'SURVEY_SCHEDULED', 'QUOTED', 'NEGOTIATION', 'WON', 'LOST'] as $st)
                                                                <option value="{{ $st }}" {{ $l->status == $st ? 'selected' : '' }}>{{ $st }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-primary fw-bold">Save Lead Changes</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">No solar leads registered yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($leads->hasPages())
                    <div class="card-footer bg-white border-top py-3">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <span class="small text-muted">Showing {{ $leads->firstItem() }} to {{ $leads->lastItem() }} of {{ $leads->total() }} leads</span>
                            {{ $leads->appends(request()->except('leads_page'))->links() }}
                        </div>
                    </div>
                @endif
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
                @if($surveys->hasPages())
                    <div class="card-footer bg-white border-top py-3">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <span class="small text-muted">Showing {{ $surveys->firstItem() }} to {{ $surveys->lastItem() }} of {{ $surveys->total() }} surveys</span>
                            {{ $surveys->appends(request()->except('surveys_page'))->links() }}
                        </div>
                    </div>
                @endif
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
                @if($quotations->hasPages())
                    <div class="card-footer bg-white border-top py-3">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <span class="small text-muted">Showing {{ $quotations->firstItem() }} to {{ $quotations->lastItem() }} of {{ $quotations->total() }} quotations</span>
                            {{ $quotations->appends(request()->except('quotes_page'))->links() }}
                        </div>
                    </div>
                @endif
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
                @if($amcs->hasPages())
                    <div class="card-footer bg-white border-top py-3">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <span class="small text-muted">Showing {{ $amcs->firstItem() }} to {{ $amcs->lastItem() }} of {{ $amcs->total() }} contracts</span>
                            {{ $amcs->appends(request()->except('amcs_page'))->links() }}
                        </div>
                    </div>
                @endif
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

<!-- Commission Project Modal -->
<div class="modal fade" id="newProjectModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form action="{{ route('solar.projects.store') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-plus text-primary me-2"></i>Commission New Solar Project</h5>
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
                    <label class="form-label small fw-bold">Project Code *</label>
                    <input type="text" name="project_code" class="form-control" required value="PRJ-SOL-{{ date('Ymd') }}-{{ rand(100, 999) }}">
                </div>
                <div class="col-12">
                    <label class="form-label small fw-bold">Project Name *</label>
                    <input type="text" name="project_name" class="form-control" required placeholder="e.g. 10kW On-Grid Rooftop Solar Plant">
                </div>
                <div class="col-12">
                    <label class="form-label small fw-bold">Site Installation Address *</label>
                    <textarea name="site_address" class="form-control" rows="2" required placeholder="Full site address where panels and inverters will be erected"></textarea>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Capacity (kW) *</label>
                    <input type="number" step="0.1" name="capacity_kw" class="form-control" required value="5" min="1">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Total Project Cost ₹ *</label>
                    <input type="number" step="0.01" name="total_project_cost" class="form-control" required value="325000">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Installation Stage *</label>
                    <select name="installation_status" class="form-select" required>
                        <option value="NOT_STARTED">NOT_STARTED</option>
                        <option value="MATERIAL_DISPATCHED">MATERIAL_DISPATCHED</option>
                        <option value="STRUCTURE_COMPLETED">STRUCTURE_COMPLETED</option>
                        <option value="WIRING_COMPLETED">WIRING_COMPLETED</option>
                        <option value="COMMISSIONED">COMMISSIONED</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Net Metering Status *</label>
                    <select name="net_metering_status" class="form-select" required>
                        <option value="APPLIED">APPLIED</option>
                        <option value="FEASIBILITY_APPROVED">FEASIBILITY_APPROVED</option>
                        <option value="METER_INSTALLED">METER_INSTALLED</option>
                        <option value="COMMISSIONED">COMMISSIONED</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Subsidy Status *</label>
                    <select name="subsidy_status" class="form-select" required>
                        <option value="NOT_APPLICABLE">NOT_APPLICABLE</option>
                        <option value="APPLIED">APPLIED</option>
                        <option value="APPROVED">APPROVED</option>
                        <option value="DISBURSED">DISBURSED</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Start Date</label>
                    <input type="date" name="start_date" class="form-control" value="{{ date('Y-m-d') }}">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary fw-bold">Register Solar Project</button>
            </div>
        </form>
    </div>
</div>

<!-- New Lead Modal -->
<div class="modal fade" id="newLeadModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('solar.leads.store') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-user-plus text-info me-2"></i>New Solar Inquiry Lead</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body row g-3">
                <div class="col-12">
                    <label class="form-label small fw-bold">Customer Name *</label>
                    <input type="text" name="customer_name" class="form-control" required placeholder="e.g. Ramesh Kumar">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Phone Number *</label>
                    <input type="text" name="phone" class="form-control" required placeholder="+91 9876543210">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Email</label>
                    <input type="email" name="email" class="form-control" placeholder="client@example.com">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">City</label>
                    <input type="text" name="city" class="form-control" placeholder="New Delhi">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Estimated kW Requirement *</label>
                    <input type="number" step="0.5" name="estimated_kw" class="form-control" required value="5" min="1">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Source</label>
                    <input type="text" name="source" class="form-control" value="Website Inquiry">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Status *</label>
                    <select name="status" class="form-select" required>
                        <option value="NEW">NEW</option>
                        <option value="CONTACTED">CONTACTED</option>
                        <option value="SURVEY_SCHEDULED">SURVEY_SCHEDULED</option>
                        <option value="QUOTED">QUOTED</option>
                        <option value="NEGOTIATION">NEGOTIATION</option>
                        <option value="WON">WON</option>
                        <option value="LOST">LOST</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-info fw-bold">Create Solar Lead</button>
            </div>
        </form>
    </div>
</div>
@endsection
