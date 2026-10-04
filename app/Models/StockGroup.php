<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'parent_id',
        'name',
        'hsn_code',
        'tax_master_id',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(StockGroup::class, 'parent_id');
    }

    public function taxMaster(): BelongsTo
    {
        return $this->belongsTo(TaxMaster::class, 'tax_master_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
