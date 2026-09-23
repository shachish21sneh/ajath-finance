<?php

namespace App\Models;

use App\Enums\PartyType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Ledger extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'ledger_group_id',
        'tax_master_id',
        'name',
        'code',
        'opening_balance',
        'opening_balance_type',
        'current_balance',
        'party_type',
        'gstin',
        'pan',
        'email',
        'phone',
        'address',
        'city',
        'state',
        'state_code',
        'pincode',
        'credit_limit',
        'credit_days',
        'bank_name',
        'bank_account_no',
        'bank_ifsc',
        'bank_branch',
        'is_active',
        'is_system',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'credit_limit' => 'decimal:2',
        'credit_days' => 'integer',
        'party_type' => PartyType::class,
        'is_active' => 'boolean',
        'is_system' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(LedgerGroup::class, 'ledger_group_id');
    }

    public function taxMaster(): BelongsTo
    {
        return $this->belongsTo(TaxMaster::class);
    }

    public function entries(): HasMany
    {
        return $this->hasMany(LedgerEntry::class);
    }

    public function bankAccount(): HasOne
    {
        return $this->hasOne(BankAccount::class);
    }

    public function scopeCustomers(Builder $query): Builder
    {
        return $query->where('party_type', PartyType::CUSTOMER);
    }

    public function scopeSuppliers(Builder $query): Builder
    {
        return $query->where('party_type', PartyType::SUPPLIER);
    }

    public function scopeBankOrCash(Builder $query): Builder
    {
        return $query->whereIn('party_type', [PartyType::BANK, PartyType::CASH]);
    }

    public function getFormattedBalanceAttribute(): string
    {
        $symbol = $this->company?->currency_symbol ?? '₹';
        $abs = number_format(abs((float) $this->current_balance), 2);
        $type = (float) $this->current_balance >= 0 ? 'Dr' : 'Cr';
        return "{$symbol} {$abs} {$type}";
    }
}
