<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FixedRoute extends Model
{
    use HasFactory;

    protected $fillable = [
        'driver_id', 'vehicle_id', 'origin', 'destination',
        'origin_coords', 'destination_coords', 'departure_time',
        'days_of_week', 'available_seats', 'status',
    ];

    protected $casts = [
        'days_of_week' => 'array',
    ];

    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_id');
    }
}
