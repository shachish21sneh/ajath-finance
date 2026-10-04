<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductInventoryComponent extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'product_id',
        'component_product_id',
        'name',
        'hsn_code',
        'gst_rate',
        'unit_price',
        'quantity',
    ];

    protected $casts = [
        'gst_rate' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'quantity' => 'decimal:2',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function componentProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'component_product_id');
    }

    public function getTotalCostAttribute(): float
    {
        return round((float) $this->quantity * (float) $this->unit_price, 2);
    }
}
