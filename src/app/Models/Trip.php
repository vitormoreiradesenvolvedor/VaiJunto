<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Trip extends Model
{
    use HasFactory;

    protected $fillable = [
        'driver_id', 'vehicle_id', 'origin', 'destination',
        'origin_coords', 'destination_coords', 'departs_at',
        'seats_total', 'status',
    ];

    protected $casts = [
        'departs_at'  => 'datetime',
        'seats_total' => 'integer',
    ];

    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function requests(): HasMany
    {
        return $this->hasMany(RideRequest::class);
    }

    public function seatsAvailable(): int
    {
        $taken = $this->requests()->where('status', 'accepted')->count();
        return max(0, $this->seats_total - $taken);
    }
}
