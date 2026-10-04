@extends('layouts.app')

@section('title', 'CRM & Sales Pipeline')

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800 fw-bold"><i class="fa-solid fa-funnel-dollar text-primary me-2"></i>CRM & Sales Opportunity Pipeline</h1>
            <p class="text-muted small mb-0">Track commercial leads, quotation stages, dealer negotiations, and closing probabilities.</p>
        </div>
        <div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newLeadModal">
                <i class="fa-solid fa-plus me-1"></i> Add Sales Lead
            </button>
        </div>
    </div>

    <!-- Metrics Header -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
                <div class="text-muted small fw-semibold">Pipeline Volume</div>
                <div class="fs-4 fw-bold text-dark mt-1">{{ $leads->count() }} Leads</div>
                <div class="small text-muted mt-1">Commercial & Retail</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
                <div class="text-muted small fw-semibold">Pipeline Value</div>
                <div class="fs-4 fw-bold text-primary mt-1">₹ {{ number_format($leads->sum('estimated_value'), 2) }}</div>
                <div class="small text-muted mt-1">Weighted Opportunity</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
                <div class="text-muted small fw-semibold">Won Deals</div>
                <div class="fs-4 fw-bold text-success mt-1">{{ $pipeline['WON']->count() }} Closed</div>
                <div class="small text-success mt-1"><i class="fa-solid fa-trophy me-1"></i>Converted to Billing</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
                <div class="text-muted small fw-semibold">Negotiating</div>
                <div class="fs-4 fw-bold text-warning mt-1">{{ $pipeline['NEGOTIATION']->count() }} Accounts</div>
                <div class="small text-muted mt-1">Final Approval Stage</div>
            </div>
        </div>
    </div>

    <!-- Kanban Stage Columns -->
    <div class="row g-3 overflow-auto flex-nowrap pb-3">
        @php
            $stages = [
                'NEW' => ['title' => 'New Leads', 'color' => 'secondary'],
                'CONTACTED' => ['title' => 'Contacted / Discovery', 'color' => 'info'],
                'PROPOSAL' => ['title' => 'Quotation Sent', 'color' => 'primary'],
                'NEGOTIATION' => ['title' => 'Negotiation', 'color' => 'warning'],
                'WON' => ['title' => 'Won / Closed', 'color' => 'success'],
                'LOST' => ['title' => 'Lost / Dropped', 'color' => 'danger'],
            ];
        @endphp

        @foreach($stages as $key => $meta)
            <div class="col-md-3" style="min-width: 280px;">
                <div class="card border-0 shadow-sm rounded-3 bg-light h-100">
                    <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                        <span class="fw-bold text-{{ $meta['color'] }}">{{ $meta['title'] }}</span>
                        <span class="badge bg-{{ $meta['color'] }} rounded-pill">{{ $pipeline[$key]->count() }}</span>
                    </div>
                    <div class="card-body p-2" style="max-height: 550px; overflow-y: auto;">
                        @forelse($pipeline[$key] as $lead)
                            <div class="card border-0 shadow-sm rounded-3 mb-2 p-3 bg-white">
                                <div class="fw-bold text-dark">{{ $lead->contact_name }}</div>
                                <div class="small text-muted mb-2">{{ $lead->company_name ?? 'Individual' }}</div>
                                <div class="small fw-semibold text-primary mb-1">
                                    <i class="fa-solid fa-box me-1"></i>{{ $lead->product_interest ?? 'Solar / Battery' }}
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top">
                                    <span class="fw-bold text-dark small">₹ {{ number_format($lead->estimated_value, 2) }}</span>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light border py-0 px-2 dropdown-toggle" data-bs-toggle="dropdown">
                                            Stage
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                            @foreach(array_keys($stages) as $st)
                                                <li>
                                                    <form action="{{ route('crm.leads.stage', $lead->id) }}" method="POST">
                                                        @csrf
                                                        <input type="hidden" name="stage" value="{{ $st }}">
                                                        <button type="submit" class="dropdown-item small {{ $lead->stage === $st ? 'active' : '' }}">
                                                            Move to {{ $stages[$st]['title'] }}
                                                        </button>
                                                    </form>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-4 text-muted small">No leads in this stage.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

<!-- Add Lead Modal -->
<div class="modal fade" id="newLeadModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('crm.leads.store') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-plus text-primary me-2"></i>Add Sales Lead</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body row g-3">
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Contact Name *</label>
                    <input type="text" name="contact_name" class="form-control" required placeholder="e.g. Rajesh Mehra">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Company / Organization</label>
                    <input type="text" name="company_name" class="form-control" placeholder="e.g. Mehra Textiles Pvt Ltd">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Phone Number *</label>
                    <input type="text" name="phone" class="form-control" required placeholder="+91 98111 22334">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Email Address</label>
                    <input type="email" name="email" class="form-control" placeholder="rajesh@mehra.com">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Product Requirement</label>
                    <input type="text" name="product_interest" class="form-control" placeholder="e.g. 50kW Rooftop Solar / 48V LiFePO4">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Estimated Deal Value (₹)</label>
                    <input type="number" step="0.01" name="estimated_value" class="form-control" value="250000">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Lead Source</label>
                    <input type="text" name="source" class="form-control" value="Website / Inbound">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Initial Pipeline Stage *</label>
                    <select name="stage" class="form-select" required>
                        <option value="NEW" selected>New Lead</option>
                        <option value="CONTACTED">Contacted / Discovery</option>
                        <option value="PROPOSAL">Quotation Sent</option>
                        <option value="NEGOTIATION">Negotiation</option>
                        <option value="WON">Won / Closed</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary fw-bold">Save Opportunity</button>
            </div>
        </form>
    </div>
</div>
@endsection
