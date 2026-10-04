@extends('layouts.app')

@section('title', 'Payroll Register & Salary Summary')

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800 fw-bold"><i class="fa-solid fa-file-invoice-dollar text-primary me-2"></i>Payroll Register & Salary Report</h1>
            <p class="text-muted small mb-0">Monthly payroll runs, statutory deductions (PF, ESI, TDS, PT), and net salary disbursements.</p>
        </div>
        <div>
            <button onclick="window.print()" class="btn btn-outline-secondary">
                <i class="fa-solid fa-print me-1"></i> Print Register
            </button>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white py-3">
            <h6 class="mb-0 fw-bold">Disbursement History</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Pay Period</th>
                            <th>Active Workforce</th>
                            <th>Gross CTC</th>
                            <th>PF / ESI / Taxes</th>
                            <th>Net Disbursed</th>
                            <th>Disbursement Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($report['runs'] as $r)
                            <tr>
                                <td><strong class="fs-6">{{ date("F", mktime(0, 0, 0, $r->month, 10)) }} {{ $r->year }}</strong></td>
                                <td>{{ $r->payslips->count() }} Employees</td>
                                <td class="fw-semibold">₹ {{ number_format($r->total_gross, 2) }}</td>
                                <td class="text-danger">- ₹ {{ number_format($r->total_deductions, 2) }}</td>
                                <td class="text-success fw-bold fs-6">₹ {{ number_format($r->total_net, 2) }}</td>
                                <td><span class="badge bg-success">DISBURSED</span></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">No payroll runs found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
