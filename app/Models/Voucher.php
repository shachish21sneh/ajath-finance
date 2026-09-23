<?php

namespace App\Models;

use App\Enums\VoucherType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Voucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'financial_year_id',
        'voucher_type',
        'voucher_no',
        'voucher_date',
        'reference_no',
        'party_ledger_id',
        'total_amount',
        'narration',
        'payment_mode',
        'cheque_no',
        'cheque_date',
        'status',
        'created_by',
    ];

    protected $casts = [
        'voucher_type' => VoucherType::class,
        'voucher_date' => 'date',
        'cheque_date' => 'date',
        'total_amount' => 'decimal:2',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function financialYear(): BelongsTo
    {
        return $this->belongsTo(FinancialYear::class);
    }

    public function partyLedger(): BelongsTo
    {
        return $this->belongsTo(Ledger::class, 'party_ledger_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(VoucherItem::class)->orderBy('line_order');
    }

    public function ledgerEntries(): HasMany
    {
        return $this->hasMany(LedgerEntry::class);
    }

    public function salesInvoice(): HasOne
    {
        return $this->hasOne(SalesInvoice::class);
    }

    public function purchaseInvoice(): HasOne
    {
        return $this->hasOne(PurchaseInvoice::class);
    }

    public function isBalanced(): bool
    {
        $debits = $this->items()->where('entry_type', 'debit')->sum('amount');
        $credits = $this->items()->where('entry_type', 'credit')->sum('amount');
        return round((float) $debits, 2) === round((float) $credits, 2);
    }
}
