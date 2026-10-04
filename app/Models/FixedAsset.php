<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FixedAsset extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'ledger_id',
        'asset_name',
        'asset_code',
        'purchase_date',
        'purchase_cost',
        'serial_number',
        'location',
        'department',
        'useful_life_years',
        'depreciation_method',
        'depreciation_rate',
        'accumulated_depreciation',
        'book_value',
        'status',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'purchase_cost' => 'decimal:2',
        'depreciation_rate' => 'decimal:2',
        'accumulated_depreciation' => 'decimal:2',
        'book_value' => 'decimal:2',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function ledger(): BelongsTo
    {
        return $this->belongsTo(Ledger::class);
    }
}
