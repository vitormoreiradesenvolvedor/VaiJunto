<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fixed_routes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('driver_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('vehicle_id')->nullable()->constrained()->nullOnDelete();
            $table->string('origin');
            $table->string('destination');
            $table->string('origin_coords')->nullable();
            $table->string('destination_coords')->nullable();
            $table->time('departure_time')->nullable();
            $table->json('days_of_week')->nullable();
            $table->unsignedTinyInteger('available_seats')->default(3);
            $table->enum('status', ['active', 'paused', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fixed_routes');
    }
};
