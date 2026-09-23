<?php

namespace App\Models;

use App\Enums\EntryType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VoucherItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'voucher_id',
        'ledger_id',
        'entry_type',
        'amount',
        'narration',
        'line_order',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'entry_type' => EntryType::class,
        'line_order' => 'integer',
    ];

    public function voucher(): BelongsTo
    {
        return $this->belongsTo(Voucher::class);
    }

    public function ledger(): BelongsTo
    {
        return $this->belongsTo(Ledger::class);
    }
}
