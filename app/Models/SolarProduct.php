<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SolarProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'product_id',
        'product_type',
        'wattage',
        'voltage',
        'efficiency_percent',
        'warranty_years',
    ];

    protected $casts = [
        'wattage' => 'decimal:2',
        'voltage' => 'decimal:2',
        'efficiency_percent' => 'decimal:2',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
