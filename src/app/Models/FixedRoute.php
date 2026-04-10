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

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function requests(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(RideRequest::class);
    }

    /** Dias em português abreviado */
    public function getDaysLabelAttribute(): string
    {
        $map = [0=>'Dom',1=>'Seg',2=>'Ter',3=>'Qua',4=>'Qui',5=>'Sex',6=>'Sáb'];
        $days = collect($this->days_of_week ?? [])->sort()->map(fn($d) => $map[$d] ?? $d);
        return $days->implode(', ');
    }

    /** Próxima ocorrência da rota (data+hora) */
    public function nextOccurrence(): \Carbon\Carbon
    {
        $days = collect($this->days_of_week ?? [])->sort()->values();
        if ($days->isEmpty()) {
            return now()->setTimeFromTimeString($this->departure_time ?? '00:00');
        }
        $now      = now();
        $todayDow = (int) $now->format('w'); // 0=Dom
        [$h, $m]  = explode(':', $this->departure_time ?? '00:00');

        foreach ($days as $dow) {
            $diff = ($dow - $todayDow + 7) % 7;
            $candidate = $now->copy()->addDays($diff)->setTime((int)$h, (int)$m, 0);
            if ($candidate->isFuture()) {
                return $candidate;
            }
        }
        // Próxima semana
        $firstDow = $days->first();
        $diff = ($firstDow - $todayDow + 7) % 7 ?: 7;
        return $now->copy()->addDays($diff)->setTime((int)$h, (int)$m, 0);
    }
}
