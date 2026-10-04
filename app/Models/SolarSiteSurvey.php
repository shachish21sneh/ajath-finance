<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SolarSiteSurvey extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'solar_lead_id',
        'customer_ledger_id',
        'survey_date',
        'surveyor_name',
        'roof_type',
        'roof_area_sqft',
        'shadow_free_area_sqft',
        'tilt_angle',
        'orientation',
        'sanctioned_load_kw',
        'monthly_consumption_kwh',
        'phase',
        'consumer_number',
        'discom_name',
        'recommended_capacity_kw',
        'notes',
    ];

    protected $casts = [
        'survey_date' => 'date',
        'roof_area_sqft' => 'decimal:2',
        'shadow_free_area_sqft' => 'decimal:2',
        'tilt_angle' => 'decimal:2',
        'sanctioned_load_kw' => 'decimal:2',
        'monthly_consumption_kwh' => 'decimal:2',
        'recommended_capacity_kw' => 'decimal:2',
    ];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(SolarLead::class, 'solar_lead_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Ledger::class, 'customer_ledger_id');
    }
}
