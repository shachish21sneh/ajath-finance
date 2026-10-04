<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class BatterySerial extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'battery_model_id',
        'warehouse_id',
        'customer_ledger_id',
        'sales_invoice_id',
        'serial_number',
        'cell_batch_number',
        'mfg_date',
        'dispatch_date',
        'qc_status',
        'current_status',
    ];

    protected $casts = [
        'mfg_date' => 'date',
        'dispatch_date' => 'date',
    ];

    public function model(): BelongsTo
    {
        return $this->belongsTo(BatteryModel::class, 'battery_model_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Ledger::class, 'customer_ledger_id');
    }

    public function salesInvoice(): BelongsTo
    {
        return $this->belongsTo(SalesInvoice::class);
    }

    public function tests(): HasMany
    {
        return $this->hasMany(BatteryTest::class);
    }

    public function warranty(): HasOne
    {
        return $this->hasOne(BatteryWarranty::class);
    }
}
