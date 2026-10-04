<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BatteryModel extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'product_id',
        'chemistry',
        'nominal_voltage',
        'capacity_ah',
        'energy_wh',
        'bms_model',
        'max_charging_current',
        'max_discharge_current',
        'warranty_months',
        'free_replacement_months',
        'pro_rata_months',
        'cell_type',
        'cell_count',
    ];

    protected $casts = [
        'nominal_voltage' => 'decimal:2',
        'capacity_ah' => 'decimal:2',
        'energy_wh' => 'decimal:2',
        'max_charging_current' => 'decimal:2',
        'max_discharge_current' => 'decimal:2',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function serials(): HasMany
    {
        return $this->hasMany(BatterySerial::class);
    }
}
