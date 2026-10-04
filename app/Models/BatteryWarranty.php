<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BatteryWarranty extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'battery_serial_id',
        'customer_ledger_id',
        'invoice_no',
        'purchase_date',
        'warranty_start_date',
        'warranty_end_date',
        'warranty_type',
        'is_active',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'warranty_start_date' => 'date',
        'warranty_end_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function batterySerial(): BelongsTo
    {
        return $this->belongsTo(BatterySerial::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Ledger::class, 'customer_ledger_id');
    }

    public function claims(): HasMany
    {
        return $this->hasMany(WarrantyClaim::class);
    }
}
