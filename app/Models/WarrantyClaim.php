<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WarrantyClaim extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'battery_warranty_id',
        'claim_no',
        'claim_date',
        'complaint_description',
        'diagnosis',
        'action_taken',
        'replacement_serial_id',
        'status',
        'resolution_date',
    ];

    protected $casts = [
        'claim_date' => 'date',
        'resolution_date' => 'date',
    ];

    public function warranty(): BelongsTo
    {
        return $this->belongsTo(BatteryWarranty::class, 'battery_warranty_id');
    }

    public function replacementSerial(): BelongsTo
    {
        return $this->belongsTo(BatterySerial::class, 'replacement_serial_id');
    }
}
