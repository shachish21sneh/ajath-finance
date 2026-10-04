<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DealerCommission extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'dealer_id',
        'sales_invoice_id',
        'sales_amount',
        'commission_rate',
        'commission_amount',
        'period_month',
        'period_year',
        'status',
        'voucher_id',
    ];

    protected $casts = [
        'sales_amount' => 'decimal:2',
        'commission_rate' => 'decimal:2',
        'commission_amount' => 'decimal:2',
    ];

    public function dealer(): BelongsTo
    {
        return $this->belongsTo(Dealer::class);
    }

    public function salesInvoice(): BelongsTo
    {
        return $this->belongsTo(SalesInvoice::class);
    }

    public function voucher(): BelongsTo
    {
        return $this->belongsTo(Voucher::class);
    }
}
