<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SolarAmc extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'solar_project_id',
        'amc_code',
        'start_date',
        'end_date',
        'annual_fee',
        'visits_per_year',
        'visits_completed',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'annual_fee' => 'decimal:2',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(SolarProject::class, 'solar_project_id');
    }
}
