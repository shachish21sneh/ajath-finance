@extends('layouts.app')

@section('title', 'Payroll & HR Management')

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800 fw-bold"><i class="fa-solid fa-users-gear text-primary me-2"></i>Payroll & Human Resources</h1>
            <p class="text-muted small mb-0">Manage employee records, Indian salary structures (PF, ESI, TDS, PT), attendance, and monthly disbursements.</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#markAttendanceModal">
                <i class="fa-solid fa-calendar-check me-1"></i> Mark Attendance
            </button>
            <button class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#processPayrollModal">
                <i class="fa-solid fa-money-check-dollar me-1"></i> Run Monthly Payroll
            </button>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEmployeeModal">
                <i class="fa-solid fa-user-plus me-1"></i> Enroll Employee
            </button>
        </div>
    </div>

    <!-- Metrics Overview -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold">Active Workforce</div>
                        <div class="fs-4 fw-bold text-dark mt-1">{{ $employees->where('status', 'active')->count() }} Employees</div>
                        <div class="small text-success mt-1"><i class="fa-solid fa-circle-check me-1"></i>100% Compliant</div>
                    </div>
                    <div class="bg-primary-subtle text-primary p-3 rounded-circle fs-4">
                        <i class="fa-solid fa-id-badge"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold">Monthly Gross Payroll</div>
                        <div class="fs-4 fw-bold text-dark mt-1">₹ {{ number_format($employees->sum('monthly_salary'), 2) }}</div>
                        <div class="small text-muted mt-1">CTC Liability</div>
                    </div>
                    <div class="bg-success-subtle text-success p-3 rounded-circle fs-4">
                        <i class="fa-solid fa-indian-rupee-sign"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold">Today's Attendance</div>
                        <div class="fs-4 fw-bold text-dark mt-1">{{ $attendances->where('attendance_date', now()->toDateString())->where('status', 'present')->count() }} Present</div>
                        <div class="small text-info mt-1"><i class="fa-solid fa-clock me-1"></i>Shift Active</div>
                    </div>
                    <div class="bg-info-subtle text-info p-3 rounded-circle fs-4">
                        <i class="fa-solid fa-clipboard-user"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold">Payroll Batches</div>
                        <div class="fs-4 fw-bold text-dark mt-1">{{ $payrollRuns->count() }} Processed</div>
                        <div class="small text-muted mt-1">Double-Entry Posted</div>
                    </div>
                    <div class="bg-warning-subtle text-warning p-3 rounded-circle fs-4">
                        <i class="fa-solid fa-file-invoice-dollar"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <ul class="nav nav-tabs mb-4" id="payrollTabs" role="tablist">
        <li class="nav-item">
            <button class="nav-link active fw-semibold" id="emp-tab" data-bs-toggle="tab" data-bs-target="#empPane" type="button">
                <i class="fa-solid fa-user-group me-1"></i> Employee Directory
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link fw-semibold" id="att-tab" data-bs-toggle="tab" data-bs-target="#attPane" type="button">
                <i class="fa-solid fa-calendar-days me-1"></i> Attendance Log
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link fw-semibold" id="runs-tab" data-bs-toggle="tab" data-bs-target="#runsPane" type="button">
                <i class="fa-solid fa-receipt me-1"></i> Payroll Disbursements
            </button>
        </li>
    </ul>

    <div class="tab-content" id="payrollTabsContent">
        <!-- Employees Pane -->
        <div class="tab-pane fade show active" id="empPane">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Code</th>
                                    <th>Employee Name</th>
                                    <th>Department</th>
                                    <th>Designation</th>
                                    <th>Joining Date</th>
                                    <th>Monthly Gross</th>
                                    <th>Net Salary</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($employees as $emp)
                                    <tr>
                                        <td><span class="badge bg-secondary font-monospace">{{ $emp->emp_code }}</span></td>
                                        <td>
                                            <div class="fw-bold">{{ $emp->full_name }}</div>
                                            <div class="small text-muted">{{ $emp->email ?? $emp->phone ?? 'No contact' }}</div>
                                        </td>
                                        <td>{{ $emp->department?->name ?? '—' }}</td>
                                        <td>{{ $emp->designation?->title ?? '—' }}</td>
                                        <td>{{ $emp->joining_date->format('d M Y') }}</td>
                                        <td class="fw-semibold">₹ {{ number_format($emp->monthly_salary, 2) }}</td>
                                        <td class="text-success fw-bold">₹ {{ number_format($emp->salaryStructure?->net_salary ?? $emp->monthly_salary * 0.85, 2) }}</td>
                                        <td>
                                            <span class="badge bg-{{ $emp->status === 'active' ? 'success' : 'secondary' }}">
                                                {{ ucfirst($emp->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4 text-muted">No employees registered yet. Click "Enroll Employee" to begin.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Attendance Pane -->
        <div class="tab-pane fade" id="attPane">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold">Recent Attendance Records</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Employee</th>
                                    <th>Status</th>
                                    <th>Hours Worked</th>
                                    <th>Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($attendances as $att)
                                    <tr>
                                        <td>{{ $att->attendance_date->format('d M Y') }}</td>
                                        <td><strong>{{ $att->employee?->full_name }}</strong> ({{ $att->employee?->emp_code }})</td>
                                        <td>
                                            <span class="badge bg-{{ $att->status === 'present' ? 'success' : ($att->status === 'half_day' ? 'warning' : 'danger') }}">
                                                {{ strtoupper($att->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $att->total_hours }} hrs</td>
                                        <td>{{ $att->remarks ?? '—' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">No attendance logs available.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payroll Runs Pane -->
        <div class="tab-pane fade" id="runsPane">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold">Disbursement History & Payslips</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Period</th>
                                    <th>Total Gross</th>
                                    <th>Statutory Deductions (PF/ESI/TDS)</th>
                                    <th>Total Net Disbursed</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($payrollRuns as $run)
                                    <tr>
                                        <td><strong class="fs-6">{{ date("F", mktime(0, 0, 0, $run->month, 10)) }} {{ $run->year }}</strong></td>
                                        <td>₹ {{ number_format($run->total_gross, 2) }}</td>
                                        <td class="text-danger">- ₹ {{ number_format($run->total_deductions, 2) }}</td>
                                        <td class="text-success fw-bold fs-6">₹ {{ number_format($run->total_net, 2) }}</td>
                                        <td><span class="badge bg-success">DISBURSED</span></td>
                                        <td>
                                            @if($run->payslips->isNotEmpty())
                                                <a href="{{ route('payroll.payslip', $run->payslips->first()->id) }}" class="btn btn-sm btn-outline-primary" target="_blank">
                                                    <i class="fa-solid fa-file-invoice me-1"></i> View Sample Payslip
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">No payroll runs executed yet.</td>
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

<!-- Enroll Employee Modal -->
<div class="modal fade" id="addEmployeeModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form action="{{ route('payroll.employees.store') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-user-plus text-primary me-2"></i>Enroll New Employee</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body row g-3">
                <div class="col-md-6">
                    <label class="form-label small fw-bold">First Name *</label>
                    <input type="text" name="first_name" class="form-control" required placeholder="e.g. Ramesh">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Last Name</label>
                    <input type="text" name="last_name" class="form-control" placeholder="e.g. Kumar">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Employee Code *</label>
                    <input type="text" name="emp_code" class="form-control" required placeholder="e.g. FZ-EMP-101">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Department</label>
                    <select name="department_id" class="form-select">
                        <option value="">Select Department</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Designation</label>
                    <select name="designation_id" class="form-select">
                        <option value="">Select Designation</option>
                        @foreach($designations as $desig)
                            <option value="{{ $desig->id }}">{{ $desig->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Monthly CTC Salary (₹) *</label>
                    <input type="number" step="0.01" name="monthly_salary" class="form-control" required placeholder="e.g. 50000">
                    <div class="form-text small">Automatically splits into Basic (50%), HRA (25%), Allowances, PF, and ESI.</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Joining Date *</label>
                    <input type="date" name="joining_date" class="form-control" required value="{{ date('Y-m-d') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Mobile Phone</label>
                    <input type="text" name="phone" class="form-control" placeholder="+91 98765 43210">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Email Address</label>
                    <input type="email" name="email" class="form-control" placeholder="ramesh@fuzurra.com">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold">PAN Number</label>
                    <input type="text" name="pan" class="form-control" placeholder="ABCDE1234F">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check me-1"></i>Save & Calculate Structure</button>
            </div>
        </form>
    </div>
</div>

<!-- Mark Attendance Modal -->
<div class="modal fade" id="markAttendanceModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('payroll.attendance.store') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Daily Attendance Entry</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body row g-3">
                <div class="col-md-12">
                    <label class="form-label small fw-bold">Employee *</label>
                    <select name="employee_id" class="form-select" required>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->full_name }} ({{ $emp->emp_code }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Date *</label>
                    <input type="date" name="attendance_date" class="form-control" required value="{{ date('Y-m-d') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Status *</label>
                    <select name="status" class="form-select" required>
                        <option value="present">Present (Full Day)</option>
                        <option value="half_day">Half Day (4 hrs)</option>
                        <option value="absent">Absent</option>
                        <option value="leave">Approved Leave</option>
                    </select>
                </div>
                <div class="col-md-12">
                    <label class="form-label small fw-bold">Remarks</label>
                    <input type="text" name="remarks" class="form-control" placeholder="Optional notes">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Mark Attendance</button>
            </div>
        </form>
    </div>
</div>

<!-- Run Monthly Payroll Modal -->
<div class="modal fade" id="processPayrollModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('payroll.process') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title fw-bold text-success"><i class="fa-solid fa-coins me-2"></i>Run Monthly Payroll Batch</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body row g-3">
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Month *</label>
                    <select name="month" class="form-select" required>
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ $m == date('n') ? 'selected' : '' }}>
                                {{ date("F", mktime(0, 0, 0, $m, 10)) }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Year *</label>
                    <input type="number" name="year" class="form-control" required value="{{ date('Y') }}">
                </div>
                <div class="col-md-12">
                    <div class="alert alert-info small mb-0">
                        <i class="fa-solid fa-circle-info me-1"></i>
                        Executing this batch will calculate individual payslips with statutory deductions (PF, ESI, TDS, PT) and prepare the accounting disbursement voucher for active workforce.
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-success fw-bold"><i class="fa-solid fa-bolt me-1"></i>Calculate & Disburse</button>
            </div>
        </form>
    </div>
</div>
@endsection
