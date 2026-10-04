<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SolarLead extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'customer_name',
        'phone',
        'email',
        'address',
        'city',
        'state',
        'pincode',
        'estimated_kw',
        'source',
        'status',
        'remarks',
    ];

    protected $casts = [
        'estimated_kw' => 'decimal:2',
    ];

    public function siteSurvey(): HasOne
    {
        return $this->hasOne(SolarSiteSurvey::class);
    }

    public function quotations(): HasMany
    {
        return $this->hasMany(SolarQuotation::class);
    }
}
