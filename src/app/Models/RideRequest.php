<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class RideRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'passenger_id', 'trip_id', 'fixed_route_id', 'origin', 'destination',
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

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }

    public function fixedRoute(): BelongsTo
    {
        return $this->belongsTo(FixedRoute::class);
    }

    public function ride(): HasOne
    {
        return $this->hasOne(Ride::class);
    }
}
