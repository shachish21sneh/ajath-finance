<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobWorker extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'ledger_id',
        'name',
        'code',
        'phone',
        'gstin',
        'address',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function ledger(): BelongsTo
    {
        return $this->belongsTo(Ledger::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(JobWorkOrder::class);
    }
}
