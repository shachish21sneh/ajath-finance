<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceTicket extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'customer_ledger_id',
        'ticket_no',
        'customer_name',
        'phone',
        'product_name',
        'serial_number',
        'complaint_details',
        'priority',
        'status',
        'assigned_technician',
        'created_date',
        'resolved_date',
    ];

    protected $casts = [
        'created_date' => 'date',
        'resolved_date' => 'date',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Ledger::class, 'customer_ledger_id');
    }

    public function visits(): HasMany
    {
        return $this->hasMany(ServiceVisit::class);
    }
}
