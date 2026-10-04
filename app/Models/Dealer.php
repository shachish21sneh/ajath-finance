<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dealer extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'ledger_id',
        'dealer_code',
        'name',
        'company_name',
        'territory',
        'gstin',
        'phone',
        'email',
        'address',
        'credit_limit',
        'credit_days',
        'price_tier',
        'outstanding_balance',
        'status',
    ];

    protected $casts = [
        'credit_limit' => 'decimal:2',
        'outstanding_balance' => 'decimal:2',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function ledger(): BelongsTo
    {
        return $this->belongsTo(Ledger::class);
    }

    public function commissions(): HasMany
    {
        return $this->hasMany(DealerCommission::class);
    }
}
