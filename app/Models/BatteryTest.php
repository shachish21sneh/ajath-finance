<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BatteryTest extends Model
{
    use HasFactory;

    protected $fillable = [
        'battery_serial_id',
        'test_date',
        'open_circuit_voltage',
        'pack_voltage',
        'internal_resistance_mohm',
        'actual_capacity_ah',
        'specific_gravity',
        'charge_test_passed',
        'discharge_test_passed',
        'bms_comm_passed',
        'qc_result',
        'technician_name',
        'certificate_no',
        'remarks',
    ];

    protected $casts = [
        'test_date' => 'date',
        'open_circuit_voltage' => 'decimal:2',
        'pack_voltage' => 'decimal:2',
        'internal_resistance_mohm' => 'decimal:2',
        'actual_capacity_ah' => 'decimal:2',
        'specific_gravity' => 'decimal:3',
        'charge_test_passed' => 'boolean',
        'discharge_test_passed' => 'boolean',
        'bms_comm_passed' => 'boolean',
    ];

    public function batterySerial(): BelongsTo
    {
        return $this->belongsTo(BatterySerial::class);
    }
}
