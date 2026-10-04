<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobWorkItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_work_order_id',
        'product_id',
        'issued_qty',
        'received_qty',
        'scrap_qty',
    ];

    protected $casts = [
        'issued_qty' => 'decimal:2',
        'received_qty' => 'decimal:2',
        'scrap_qty' => 'decimal:2',
    ];

    public function jobWorkOrder(): BelongsTo
    {
        return $this->belongsTo(JobWorkOrder::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
