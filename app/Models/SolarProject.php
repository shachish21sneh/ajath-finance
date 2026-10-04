<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SolarProject extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'customer_ledger_id',
        'quotation_id',
        'project_code',
        'project_name',
        'site_address',
        'capacity_kw',
        'total_project_cost',
        'subsidy_status',
        'net_metering_status',
        'installation_status',
        'start_date',
        'commissioning_date',
        'installer_lead',
    ];

    protected $casts = [
        'start_date' => 'date',
        'commissioning_date' => 'date',
        'capacity_kw' => 'decimal:2',
        'total_project_cost' => 'decimal:2',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Ledger::class, 'customer_ledger_id');
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(SolarQuotation::class, 'quotation_id');
    }

    public function amcs(): HasMany
    {
        return $this->hasMany(SolarAmc::class);
    }
}
