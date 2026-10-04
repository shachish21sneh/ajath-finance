<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceVisit extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_ticket_id',
        'visit_date',
        'technician_name',
        'findings',
        'action_taken',
        'parts_used',
        'service_charges',
        'customer_signature_name',
        'status',
    ];

    protected $casts = [
        'visit_date' => 'date',
        'service_charges' => 'decimal:2',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(ServiceTicket::class, 'service_ticket_id');
    }
}
