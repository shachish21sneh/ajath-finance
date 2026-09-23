<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'stock_group_id',
        'stock_category_id',
        'brand_id',
        'unit_id',
        'tax_master_id',
        'name',
        'sku',
        'barcode',
        'item_type',
        'hsn_code',
        'sac_code',
        'purchase_price',
        'selling_price',
        'mrp',
        'opening_stock',
        'opening_stock_valuation',
        'current_stock',
        'reorder_level',
        'description',
        'is_active',
    ];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'mrp' => 'decimal:2',
        'opening_stock' => 'decimal:2',
        'opening_stock_valuation' => 'decimal:2',
        'current_stock' => 'decimal:2',
        'reorder_level' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function stockGroup(): BelongsTo
    {
        return $this->belongsTo(StockGroup::class);
    }

    public function stockCategory(): BelongsTo
    {
        return $this->belongsTo(StockCategory::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function taxMaster(): BelongsTo
    {
        return $this->belongsTo(TaxMaster::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function scopeGoods(Builder $query): Builder
    {
        return $query->where('item_type', 'goods');
    }

    public function scopeServices(Builder $query): Builder
    {
        return $query->where('item_type', 'service');
    }

    public function isService(): bool
    {
        return $this->item_type === 'service';
    }

    public function isLowStock(): bool
    {
        return !$this->isService() && (float) $this->current_stock <= (float) $this->reorder_level;
    }
}
