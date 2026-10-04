<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductionOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'branch_id',
        'bom_id',
        'warehouse_id',
        'order_no',
        'order_date',
        'planned_qty',
        'completed_qty',
        'scrap_qty',
        'total_cost',
        'cost_per_unit',
        'status',
        'start_date',
        'completion_date',
        'remarks',
    ];

    protected $casts = [
        'order_date' => 'date',
        'start_date' => 'date',
        'completion_date' => 'date',
        'planned_qty' => 'decimal:2',
        'completed_qty' => 'decimal:2',
        'scrap_qty' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'cost_per_unit' => 'decimal:2',
    ];

    public function bom(): BelongsTo
    {
        return $this->belongsTo(Bom::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function materials(): HasMany
    {
        return $this->hasMany(ProductionMaterial::class);
    }
}
