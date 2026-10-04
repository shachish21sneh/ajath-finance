<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SolarQuotation extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'solar_lead_id',
        'customer_ledger_id',
        'quotation_no',
        'quotation_date',
        'system_capacity_kw',
        'panel_model',
        'panel_qty',
        'inverter_model',
        'inverter_qty',
        'battery_model',
        'battery_qty',
        'structure_type',
        'system_cost',
        'gst_amount',
        'subsidy_amount',
        'net_payable',
        'estimated_monthly_gen_units',
        'payback_years',
        'status',
    ];

    protected $casts = [
        'quotation_date' => 'date',
        'system_capacity_kw' => 'decimal:2',
        'system_cost' => 'decimal:2',
        'gst_amount' => 'decimal:2',
        'subsidy_amount' => 'decimal:2',
        'net_payable' => 'decimal:2',
        'estimated_monthly_gen_units' => 'decimal:2',
        'payback_years' => 'decimal:1',
    ];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(SolarLead::class, 'solar_lead_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Ledger::class, 'customer_ledger_id');
    }

    public function project(): HasOne
    {
        return $this->hasOne(SolarProject::class, 'quotation_id');
    }
}
