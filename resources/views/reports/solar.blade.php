@extends('layouts.app')

@section('title', 'Solar Projects & Commissioning Report')

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800 fw-bold"><i class="fa-solid fa-solar-panel text-warning me-2"></i>Solar Projects & Commissioning Report</h1>
            <p class="text-muted small mb-0">Rooftop and ground-mounted EPC installations, cumulative capacity kW, net-metering approvals, and subsidy disbursements.</p>
        </div>
        <div>
            <button onclick="window.print()" class="btn btn-outline-secondary">
                <i class="fa-solid fa-print me-1"></i> Print Report
            </button>
        </div>
    </div>

    <!-- Overview Metrics -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
                <div class="text-muted small fw-semibold">Total Cumulative Capacity</div>
                <div class="display-6 fw-bold text-success mt-1">{{ $report['total_capacity_kw'] }} kW</div>
                <div class="small text-muted mt-1">Solar PV Generation</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
                <div class="text-muted small fw-semibold">Aggregate Project Value</div>
                <div class="fs-4 fw-bold text-dark mt-1">₹ {{ number_format($report['total_value'], 2) }}</div>
                <div class="small text-muted mt-1">Gross Contract Value</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
                <div class="text-muted small fw-semibold">Commissioned Plants</div>
                <div class="fs-4 fw-bold text-primary mt-1">{{ $report['commissioned_count'] }} Plants</div>
                <div class="small text-success mt-1"><i class="fa-solid fa-check me-1"></i>Grid Net-metered</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
                <div class="text-muted small fw-semibold">Under Installation</div>
                <div class="fs-4 fw-bold text-warning mt-1">{{ $report['in_progress_count'] }} Sites</div>
                <div class="small text-muted mt-1">Execution Stage</div>
            </div>
        </div>
    </div>

    <!-- Projects Table -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white py-3">
            <h6 class="mb-0 fw-bold">All Solar Installation Projects</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Project Code</th>
                            <th>Customer & Site</th>
                            <th>Capacity</th>
                            <th>Contract Value</th>
                            <th>Net-Metering</th>
                            <th>Subsidy</th>
                            <th>Stage</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($report['projects'] as $p)
                            <tr>
                                <td><span class="badge bg-secondary font-monospace">{{ $p->project_code }}</span></td>
                                <td>
                                    <div class="fw-bold">{{ $p->customer?->name }}</div>
                                    <div class="small text-muted">{{ Str::limit($p->site_address, 30) }}</div>
                                </td>
                                <td><span class="badge bg-warning text-dark fs-6">{{ $p->capacity_kw }} kW</span></td>
                                <td class="fw-bold">₹ {{ number_format($p->total_project_cost, 2) }}</td>
                                <td><span class="badge bg-info text-dark">{{ $p->net_metering_status }}</span></td>
                                <td><span class="badge bg-success">{{ $p->subsidy_status }}</span></td>
                                <td><span class="badge bg-{{ $p->installation_status === 'COMMISSIONED' ? 'success' : 'primary' }}">{{ $p->installation_status }}</span></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">No solar projects recorded.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
