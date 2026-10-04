<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salary Payslip - {{ $payslip->employee?->full_name }} ({{ date("M Y", mktime(0, 0, 0, $payslip->payrollRun?->month, 10)) }})</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .payslip-container { max-width: 800px; margin: 30px auto; background: #fff; padding: 40px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border-radius: 8px; }
        @media print {
            body { background: #fff; }
            .no-print { display: none !important; }
            .payslip-container { box-shadow: none; margin: 0; padding: 0; width: 100%; max-width: 100%; }
        }
    </style>
</head>
<body>
    <div class="container text-center my-3 no-print">
        <button onclick="window.print()" class="btn btn-primary"><i class="fa-solid fa-print me-2"></i>Print Payslip</button>
        <button onclick="window.close()" class="btn btn-secondary ms-2">Close</button>
    </div>

    <div class="payslip-container">
        <!-- Header -->
        <div class="text-center border-bottom pb-3 mb-4">
            <h3 class="fw-bold text-dark mb-1">{{ $company->name }}</h3>
            <p class="text-muted small mb-1">{{ $company->address }}, {{ $company->city }}, {{ $company->state }} - {{ $company->pincode }}</p>
            <p class="text-muted small mb-0">GSTIN: <strong>{{ $company->gstin ?? 'N/A' }}</strong> | PAN: <strong>{{ $company->pan ?? 'N/A' }}</strong></p>
            <h5 class="fw-bold mt-3 text-primary text-uppercase letter-spacing-1">Salary Payslip</h5>
            <div class="badge bg-light text-dark border px-3 py-2 fs-6">
                Pay Period: {{ date("F Y", mktime(0, 0, 0, $payslip->payrollRun?->month, 10)) }}
            </div>
        </div>

        <!-- Employee Info -->
        <div class="row g-2 mb-4 bg-light p-3 rounded">
            <div class="col-6">
                <table class="table table-sm table-borderless mb-0">
                    <tr><td class="text-muted" width="40%">Employee ID:</td><td class="fw-bold">{{ $payslip->employee?->emp_code }}</td></tr>
                    <tr><td class="text-muted">Employee Name:</td><td class="fw-bold">{{ $payslip->employee?->full_name }}</td></tr>
                    <tr><td class="text-muted">Department:</td><td>{{ $payslip->employee?->department?->name ?? 'Operations' }}</td></tr>
                    <tr><td class="text-muted">Designation:</td><td>{{ $payslip->employee?->designation?->title ?? 'Executive' }}</td></tr>
                </table>
            </div>
            <div class="col-6 border-start">
                <table class="table table-sm table-borderless mb-0">
                    <tr><td class="text-muted" width="40%">Joining Date:</td><td>{{ $payslip->employee?->joining_date?->format('d-M-Y') }}</td></tr>
                    <tr><td class="text-muted">PAN Number:</td><td>{{ $payslip->employee?->pan ?? 'N/A' }}</td></tr>
                    <tr><td class="text-muted">Bank A/c:</td><td>{{ $payslip->employee?->bank_account_no ?? 'HDFC Bank - 502000...' }}</td></tr>
                    <tr><td class="text-muted">Payment Status:</td><td><span class="badge bg-success">PAID</span></td></tr>
                </table>
            </div>
        </div>

        <!-- Earnings and Deductions Table -->
        <div class="row g-0 border mb-4 rounded overflow-hidden">
            <!-- Earnings -->
            <div class="col-6 border-end">
                <div class="bg-primary text-white text-center py-2 fw-bold">Earnings</div>
                <table class="table table-sm mb-0">
                    <tbody>
                        <tr><td>Basic Salary</td><td class="text-end">₹ {{ number_format($payslip->basic, 2) }}</td></tr>
                        <tr><td>House Rent Allowance (HRA)</td><td class="text-end">₹ {{ number_format($payslip->hra, 2) }}</td></tr>
                        <tr><td>Conveyance Allowance</td><td class="text-end">₹ {{ number_format($payslip->conveyance, 2) }}</td></tr>
                        <tr><td>Special Allowance</td><td class="text-end">₹ {{ number_format($payslip->special_allowance, 2) }}</td></tr>
                    </tbody>
                    <tfoot class="border-top fw-bold bg-light">
                        <tr><td>Gross Earnings</td><td class="text-end text-primary">₹ {{ number_format($payslip->gross_salary, 2) }}</td></tr>
                    </tfoot>
                </table>
            </div>
            <!-- Deductions -->
            <div class="col-6">
                <div class="bg-danger text-white text-center py-2 fw-bold">Deductions</div>
                <table class="table table-sm mb-0">
                    <tbody>
                        <tr><td>Provident Fund (PF)</td><td class="text-end">₹ {{ number_format($payslip->pf, 2) }}</td></tr>
                        <tr><td>Employee State Insurance (ESI)</td><td class="text-end">₹ {{ number_format($payslip->esi, 2) }}</td></tr>
                        <tr><td>Professional Tax (PT)</td><td class="text-end">₹ {{ number_format($payslip->pt, 2) }}</td></tr>
                        <tr><td>Tax Deducted at Source (TDS)</td><td class="text-end">₹ {{ number_format($payslip->tds, 2) }}</td></tr>
                    </tbody>
                    <tfoot class="border-top fw-bold bg-light">
                        <tr><td>Total Deductions</td><td class="text-end text-danger">₹ {{ number_format($payslip->total_deductions, 2) }}</td></tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Net Take Home -->
        <div class="p-3 bg-light border rounded text-center mb-4">
            <div class="text-muted small text-uppercase fw-semibold">Net Salary Payable / Disbursed</div>
            <div class="display-6 fw-bold text-success my-1">₹ {{ number_format($payslip->net_salary, 2) }}</div>
            <div class="small text-muted font-italic">({{ \App\Helpers\AccountingHelper::amountToWords($payslip->net_salary) }})</div>
        </div>

        <!-- Signatures -->
        <div class="row pt-5 text-center">
            <div class="col-6">
                <div class="border-top pt-2 mx-4 text-muted small">Employee Signature</div>
            </div>
            <div class="col-6">
                <div class="border-top pt-2 mx-4 text-muted small">Authorized Signatory<br><strong>{{ $company->name }}</strong></div>
            </div>
        </div>

        <div class="text-center text-muted small mt-4 pt-3 border-top">
            This is a computer generated payslip and requires no physical seal. Generated by FUZURRA ERP.
        </div>
    </div>
</body>
</html>
