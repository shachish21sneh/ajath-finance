<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CrmLead extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'contact_name',
        'company_name',
        'phone',
        'email',
        'source',
        'product_interest',
        'estimated_value',
        'assigned_to',
        'stage',
        'next_follow_up_date',
        'notes',
    ];

    protected $casts = [
        'estimated_value' => 'decimal:2',
        'next_follow_up_date' => 'date',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
