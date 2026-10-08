<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalesInvoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'financial_year_id',
        'voucher_id',
        'customer_ledger_id',
        'shipping_name',
        'shipping_phone',
        'shipping_email',
        'shipping_gstin',
        'shipping_address',
        'shipping_city',
        'shipping_state',
        'shipping_state_code',
        'shipping_pincode',
        'invoice_type',
        'invoice_no',
        'invoice_date',
        'due_date',
        'subtotal',
        'discount_amount',
        'taxable_amount',
        'cgst_amount',
        'sgst_amount',
        'igst_amount',
        'cess_amount',
        'round_off',
        'grand_total',
        'paid_amount',
        'due_amount',
        'payment_status',
        'payment_method',
        'cash_tendered',
        'change_returned',
        'notes',
        'terms_conditions',
        'status',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'taxable_amount' => 'decimal:2',
        'cgst_amount' => 'decimal:2',
        'sgst_amount' => 'decimal:2',
        'igst_amount' => 'decimal:2',
        'cess_amount' => 'decimal:2',
        'round_off' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'due_amount' => 'decimal:2',
        'cash_tendered' => 'decimal:2',
        'change_returned' => 'decimal:2',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function financialYear(): BelongsTo
    {
        return $this->belongsTo(FinancialYear::class);
    }

    public function voucher(): BelongsTo
    {
        return $this->belongsTo(Voucher::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Ledger::class, 'customer_ledger_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SalesInvoiceItem::class);
    }

    public function isPos(): bool
    {
        return $this->invoice_type === 'pos';
    }

    public function hasCustomShippingAddress(): bool
    {
        return !empty($this->shipping_name) || !empty($this->shipping_address);
    }
}
