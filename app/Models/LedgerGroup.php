<?php

namespace App\Models;

use App\Enums\LedgerNature;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LedgerGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'parent_id',
        'name',
        'slug',
        'nature',
        'affects_gross_profit',
        'is_system',
        'order',
    ];

    protected $casts = [
        'nature' => LedgerNature::class,
        'affects_gross_profit' => 'boolean',
        'is_system' => 'boolean',
        'order' => 'integer',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(LedgerGroup::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(LedgerGroup::class, 'parent_id')->orderBy('order');
    }

    public function ledgers(): HasMany
    {
        return $this->hasMany(Ledger::class);
    }
}
