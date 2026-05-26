<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class User extends Authenticatable
{
    use HasFactory;

    protected $fillable = [
        'name', 'email', 'avatar', 'role', 'is_active',
        'points_balance', 'last_lat', 'last_lng', 'available_until',
    ];

    protected $casts = [
        'is_active'       => 'boolean',
        'points_balance'  => 'integer',
        'last_lat'        => 'float',
        'last_lng'        => 'float',
        'available_until' => 'datetime',
    ];

    // Permite setar "last_known_coords" como "-21.23,-45.00" e popula last_lat/last_lng
    public function setLastKnownCoordsAttribute(string $value): void
    {
        [$lat, $lng] = explode(',', $value);
        $this->attributes['last_lat'] = (float) $lat;
        $this->attributes['last_lng'] = (float) $lng;
    }

    public function rides(): HasMany
    {
        return $this->hasMany(Ride::class, 'driver_id');
    }

    public function vehicle(): HasOne
    {
        return $this->hasOne(Vehicle::class);
    }

    public function pointTransactions(): HasMany
    {
        return $this->hasMany(PointTransaction::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }
}
