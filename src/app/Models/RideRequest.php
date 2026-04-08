<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RideRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'passenger_id', 'origin', 'destination',
        'origin_coords', 'destination_coords',
        'scheduled_for', 'seats_needed', 'status',
    ];

    protected $casts = [
        'scheduled_for' => 'datetime',
        'seats_needed'  => 'integer',
    ];

    public function passenger(): BelongsTo
    {
        return $this->belongsTo(User::class, 'passenger_id');
    }
}
