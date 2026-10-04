@extends('layouts.app')

@section('title', 'Service & Support Management')

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800 fw-bold"><i class="fa-solid fa-headset text-info me-2"></i>Service & Warranty Support Management</h1>
            <p class="text-muted small mb-0">Helpdesk tickets, field technician visits, diagnosis reports, spare parts tracking, and SLA turnaround.</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-info text-dark" data-bs-toggle="modal" data-bs-target="#newVisitModal">
                <i class="fa-solid fa-clipboard-check me-1"></i> Log Field Visit
            </button>
            <button class="btn btn-info text-white fw-bold" data-bs-toggle="modal" data-bs-target="#newTicketModal">
                <i class="fa-solid fa-ticket me-1"></i> Open Service Ticket
            </button>
        </div>
    </div>

    <!-- Metrics Overview -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
                <div class="text-muted small fw-semibold">Open Complaints</div>
                <div class="fs-4 fw-bold text-danger mt-1">{{ $tickets->where('status', 'OPEN')->count() }} Tickets</div>
                <div class="small text-muted mt-1">Pending Technician Action</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
                <div class="text-muted small fw-semibold">In Progress</div>
                <div class="fs-4 fw-bold text-warning mt-1">{{ $tickets->where('status', 'IN_PROGRESS')->count() }} Active</div>
                <div class="small text-muted mt-1">Field Visits Underway</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
                <div class="text-muted small fw-semibold">Resolved Cases</div>
                <div class="fs-4 fw-bold text-success mt-1">{{ $tickets->where('status', 'RESOLVED')->count() }} Closed</div>
                <div class="small text-success mt-1"><i class="fa-solid fa-check-double me-1"></i>100% Customer Signoff</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
                <div class="text-muted small fw-semibold">Technician Visits</div>
                <div class="fs-4 fw-bold text-dark mt-1">{{ $visits->count() }} Visits</div>
                <div class="small text-muted mt-1">Field Logs Recorded</div>
            </div>
        </div>
    </div>

    <!-- Tickets Table -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-header bg-white py-3">
            <h6 class="mb-0 fw-bold">Customer Complaints & Service Tickets</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Ticket #</th>
                            <th>Customer & Phone</th>
                            <th>Product & Serial</th>
                            <th>Complaint Details</th>
                            <th>Priority</th>
                            <th>Technician</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tickets as $t)
                            <tr>
                                <td><span class="badge bg-secondary font-monospace">{{ $t->ticket_no }}</span></td>
                                <td>
                                    <div class="fw-bold">{{ $t->customer_name }}</div>
                                    <div class="small text-muted">{{ $t->phone }}</div>
                                </td>
                                <td>
                                    <div>{{ $t->product_name }}</div>
                                    <small class="text-muted font-monospace">{{ $t->serial_number ?? 'No Serial' }}</small>
                                </td>
                                <td><span class="text-wrap" style="max-width: 250px;">{{ Str::limit($t->complaint_details, 45) }}</span></td>
                                <td>
                                    <span class="badge bg-{{ $t->priority === 'CRITICAL' ? 'danger' : ($t->priority === 'HIGH' ? 'warning text-dark' : 'info text-dark') }}">
                                        {{ $t->priority }}
                                    </span>
                                </td>
                                <td>{{ $t->assigned_technician ?? 'Unassigned' }}</td>
                                <td>{{ $t->created_date->format('d M Y') }}</td>
                                <td>
                                    <span class="badge bg-{{ $t->status === 'RESOLVED' ? 'success' : ($t->status === 'OPEN' ? 'danger' : 'warning text-dark') }}">
                                        {{ $t->status }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">No open service tickets found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- New Ticket Modal -->
<div class="modal fade" id="newTicketModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('service.tickets.store') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-headset text-info me-2"></i>Log Service Complaint</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body row g-3">
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Ticket # *</label>
                    <input type="text" name="ticket_no" class="form-control" required value="SRV-{{ date('Ymd') }}-{{ rand(10, 99) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Complaint Date *</label>
                    <input type="date" name="created_date" class="form-control" required value="{{ date('Y-m-d') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Customer Name *</label>
                    <input type="text" name="customer_name" class="form-control" required placeholder="e.g. Anand Sharma">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Contact Phone *</label>
                    <input type="text" name="phone" class="form-control" required placeholder="+91 98765 43210">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Product Name *</label>
                    <input type="text" name="product_name" class="form-control" required placeholder="e.g. Fuzurra 51.2V 100Ah LiFePO4">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Serial Number</label>
                    <input type="text" name="serial_number" class="form-control" placeholder="e.g. FZ-512100-2026-0001">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Priority Level *</label>
                    <select name="priority" class="form-select" required>
                        <option value="MEDIUM" selected>Medium (Standard Turnaround)</option>
                        <option value="HIGH">High (Commercial Installation)</option>
                        <option value="CRITICAL">Critical (Total Power Outage)</option>
                        <option value="LOW">Low (General Query / Inspection)</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Assign Technician</label>
                    <input type="text" name="assigned_technician" class="form-control" value="Sunil Verma (Senior Tech)">
                </div>
                <div class="col-md-12">
                    <label class="form-label small fw-bold">Complaint Description *</label>
                    <textarea name="complaint_details" class="form-control" rows="3" required placeholder="Describe issue (e.g. Inverter displaying Error 04 BMS Communication timeout)..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-info text-white fw-bold">Submit Service Ticket</button>
            </div>
        </form>
    </div>
</div>
@endsection
