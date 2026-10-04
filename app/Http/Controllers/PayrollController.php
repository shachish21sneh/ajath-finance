<?php

namespace App\Http\Controllers;

use App\Helpers\AccountingHelper;
use App\Models\Attendance;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\FinancialYear;
use App\Models\Leave;
use App\Models\Ledger;
use App\Models\PayrollRun;
use App\Models\Payslip;
use App\Models\SalaryStructure;
use App\Services\AccountingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PayrollController extends Controller
{
    public function __construct(protected AccountingService $accountingService) {}

    public function index(Request $request): View
    {
        $company = AccountingHelper::getActiveCompany();
        $employees = Employee::with(['department', 'designation', 'salaryStructure'])
            ->where('company_id', $company->id)
            ->latest()
            ->get();

        $departments = Department::where('company_id', $company->id)->get();
        $designations = Designation::where('company_id', $company->id)->get();
        $attendances = Attendance::with('employee')
            ->where('company_id', $company->id)
            ->latest('attendance_date')
            ->take(50)
            ->get();

        $leaves = Leave::with('employee')
            ->where('company_id', $company->id)
            ->latest()
            ->take(20)
            ->get();

        $payrollRuns = PayrollRun::with('payslips')
            ->where('company_id', $company->id)
            ->latest('year')
            ->latest('month')
            ->get();

        return view('payroll.index', compact('company', 'employees', 'departments', 'designations', 'attendances', 'leaves', 'payrollRuns'));
    }

    public function storeEmployee(Request $request)
    {
        $company = AccountingHelper::getActiveCompany();
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'emp_code' => 'required|string|unique:employees,emp_code',
            'department_id' => 'nullable|exists:departments,id',
            'designation_id' => 'nullable|exists:designations,id',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'pan' => 'nullable|string',
            'joining_date' => 'required|date',
            'monthly_salary' => 'required|numeric|min:0',
        ]);

        $validated['company_id'] = $company->id;
        $employee = Employee::create($validated);

        // Auto calculate default salary components based on Indian standard breakdown
        $gross = (float) $validated['monthly_salary'];
        $basic = round($gross * 0.50, 2); // 50% Basic
        $hra = round($gross * 0.25, 2);   // 25% HRA
        $conveyance = round($gross * 0.10, 2);
        $special = round($gross - ($basic + $hra + $conveyance), 2);
        $pf = round(min($basic, 15000) * 0.12, 2); // 12% PF
        $esi = $gross <= 21000 ? round($gross * 0.0075, 2) : 0.00;
        $pt = 200.00; // Professional tax
        $tds = $gross > 50000 ? round($gross * 0.05, 2) : 0.00;
        $net = $gross - ($pf + $esi + $pt + $tds);

        SalaryStructure::create([
            'company_id' => $company->id,
            'employee_id' => $employee->id,
            'basic_salary' => $basic,
            'hra' => $hra,
            'conveyance' => $conveyance,
            'special_allowance' => $special,
            'pf_deduction' => $pf,
            'esi_deduction' => $esi,
            'pt_deduction' => $pt,
            'tds_deduction' => $tds,
            'gross_salary' => $gross,
            'net_salary' => $net,
        ]);

        return redirect()->route('payroll.index')->with('success', "Employee {$employee->full_name} ({$employee->emp_code}) enrolled successfully.");
    }

    public function storeAttendance(Request $request)
    {
        $company = AccountingHelper::getActiveCompany();
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'attendance_date' => 'required|date',
            'status' => 'required|in:present,absent,half_day,leave,holiday,overtime',
            'total_hours' => 'nullable|numeric|min:0|max:24',
            'remarks' => 'nullable|string',
        ]);

        $validated['company_id'] = $company->id;
        $validated['total_hours'] = $validated['total_hours'] ?? ($validated['status'] === 'present' ? 8.0 : ($validated['status'] === 'half_day' ? 4.0 : 0.0));

        Attendance::updateOrCreate(
            ['employee_id' => $validated['employee_id'], 'attendance_date' => $validated['attendance_date']],
            $validated
        );

        return redirect()->route('payroll.index')->with('success', 'Attendance record marked successfully.');
    }

    public function processPayroll(Request $request)
    {
        $company = AccountingHelper::getActiveCompany();
        $fy = AccountingHelper::getActiveFinancialYear();

        $validated = $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020|max:2035',
        ]);

        $existing = PayrollRun::where('company_id', $company->id)
            ->where('month', $validated['month'])
            ->where('year', $validated['year'])
            ->first();

        if ($existing) {
            return redirect()->route('payroll.index')->with('error', "Payroll for Month {$validated['month']}/{$validated['year']} is already processed.");
        }

        return DB::transaction(function () use ($company, $fy, $validated) {
            $employees = Employee::with('salaryStructure')
                ->where('company_id', $company->id)
                ->where('status', 'active')
                ->get();

            if ($employees->isEmpty()) {
                return redirect()->route('payroll.index')->with('error', 'No active employees found to process payroll.');
            }

            $totalGross = 0.0;
            $totalDeductions = 0.0;
            $totalNet = 0.0;

            $payrollRun = PayrollRun::create([
                'company_id' => $company->id,
                'financial_year_id' => $fy?->id,
                'month' => $validated['month'],
                'year' => $validated['year'],
                'total_gross' => 0,
                'total_deductions' => 0,
                'total_net' => 0,
                'status' => 'approved',
                'processed_by' => auth()->id(),
            ]);

            foreach ($employees as $emp) {
                $struct = $emp->salaryStructure;
                $gross = $struct ? (float) $struct->gross_salary : (float) $emp->monthly_salary;
                $basic = $struct ? (float) $struct->basic_salary : round($gross * 0.5, 2);
                $hra = $struct ? (float) $struct->hra : round($gross * 0.25, 2);
                $conveyance = $struct ? (float) $struct->conveyance : round($gross * 0.1, 2);
                $special = $struct ? (float) $struct->special_allowance : round($gross - ($basic + $hra + $conveyance), 2);
                $pf = $struct ? (float) $struct->pf_deduction : round(min($basic, 15000) * 0.12, 2);
                $esi = $struct ? (float) $struct->esi_deduction : 0.0;
                $pt = $struct ? (float) $struct->pt_deduction : 200.0;
                $tds = $struct ? (float) $struct->tds_deduction : 0.0;
                $deductions = $pf + $esi + $pt + $tds;
                $net = $gross - $deductions;

                $totalGross += $gross;
                $totalDeductions += $deductions;
                $totalNet += $net;

                Payslip::create([
                    'company_id' => $company->id,
                    'payroll_run_id' => $payrollRun->id,
                    'employee_id' => $emp->id,
                    'basic' => $basic,
                    'hra' => $hra,
                    'conveyance' => $conveyance,
                    'special_allowance' => $special,
                    'gross_salary' => $gross,
                    'pf' => $pf,
                    'esi' => $esi,
                    'pt' => $pt,
                    'tds' => $tds,
                    'total_deductions' => $deductions,
                    'net_salary' => $net,
                    'payment_status' => 'paid',
                    'payment_date' => now(),
                ]);
            }

            $payrollRun->update([
                'total_gross' => $totalGross,
                'total_deductions' => $totalDeductions,
                'total_net' => $totalNet,
            ]);

            // Optional: Post automatic Journal Voucher for Payroll
            $salaryExpenseLedger = Ledger::where('company_id', $company->id)->where('code', 'like', '%EXP%')->first();
            $salaryPayableLedger = Ledger::where('company_id', $company->id)->where('code', 'like', '%CASH%')->first();

            return redirect()->route('payroll.index')->with('success', "Payroll for Month {$validated['month']}/{$validated['year']} successfully generated for {$employees->count()} employees. Total Net Disbursement: ₹ " . number_format($totalNet, 2));
        });
    }

    public function showPayslip(Payslip $payslip): View
    {
        $company = AccountingHelper::getActiveCompany();
        $payslip->load(['employee.department', 'employee.designation', 'payrollRun']);
        return view('payroll.payslip', compact('company', 'payslip'));
    }
}
