<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ride extends Model
{
    use HasFactory;

    protected $fillable = [
        'driver_id', 'passenger_id', 'vehicle_id', 'ride_request_id',
        'fixed_route_id', 'type', 'status', 'started_at', 'completed_at',
        'cancelled_at', 'cancel_reason', 'arrived_at', 'passenger_boarded_at',
    ];

    protected $casts = [
        'started_at'            => 'datetime',
        'completed_at'          => 'datetime',
        'cancelled_at'          => 'datetime',
        'arrived_at'            => 'datetime',
        'passenger_boarded_at'  => 'datetime',
    ];

    public function rideRequest(): BelongsTo
    {
        return $this->belongsTo(RideRequest::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function passenger(): BelongsTo
    {
        return $this->belongsTo(User::class, 'passenger_id');
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(Rating::class);
    }
}
