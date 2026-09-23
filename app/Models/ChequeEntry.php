<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChequeEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'voucher_id',
        'bank_account_id',
        'cheque_number',
        'cheque_date',
        'party_name',
        'amount',
        'type',
        'status',
        'clearing_date',
        'remarks',
    ];

    protected $casts = [
        'cheque_date' => 'date',
        'clearing_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function voucher(): BelongsTo
    {
        return $this->belongsTo(Voucher::class);
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }
}
