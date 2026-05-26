<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'model', 'plate', 'color', 'year', 'seats'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
