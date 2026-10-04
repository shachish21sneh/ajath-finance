<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payslip extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'payroll_run_id',
        'employee_id',
        'basic',
        'hra',
        'conveyance',
        'special_allowance',
        'gross_salary',
        'pf',
        'esi',
        'pt',
        'tds',
        'total_deductions',
        'net_salary',
        'payment_status',
        'payment_date',
    ];

    protected $casts = [
        'basic' => 'decimal:2',
        'hra' => 'decimal:2',
        'conveyance' => 'decimal:2',
        'special_allowance' => 'decimal:2',
        'gross_salary' => 'decimal:2',
        'pf' => 'decimal:2',
        'esi' => 'decimal:2',
        'pt' => 'decimal:2',
        'tds' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'net_salary' => 'decimal:2',
        'payment_date' => 'date',
    ];

    public function payrollRun(): BelongsTo
    {
        return $this->belongsTo(PayrollRun::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
