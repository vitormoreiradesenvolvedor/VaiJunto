<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ride_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('passenger_id')->constrained('users')->cascadeOnDelete();
            $table->string('origin');
            $table->string('destination');
            $table->string('origin_coords');
            $table->string('destination_coords')->nullable();
            $table->timestamp('scheduled_for');
            $table->unsignedTinyInteger('seats_needed')->default(1);
            $table->enum('status', ['pending', 'accepted', 'rejected', 'cancelled'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ride_requests');
    }
};
