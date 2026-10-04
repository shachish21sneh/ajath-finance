<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobWorkOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'job_worker_id',
        'order_no',
        'order_date',
        'expected_return_date',
        'charges',
        'status',
        'remarks',
    ];

    protected $casts = [
        'order_date' => 'date',
        'expected_return_date' => 'date',
        'charges' => 'decimal:2',
    ];

    public function jobWorker(): BelongsTo
    {
        return $this->belongsTo(JobWorker::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(JobWorkItem::class);
    }
}
