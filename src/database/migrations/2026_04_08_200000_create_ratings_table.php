<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ride_id')->constrained()->cascadeOnDelete();
            $table->foreignId('rater_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('ratee_id')->constrained('users')->cascadeOnDelete();
            $table->tinyInteger('stars'); // 1–5
            $table->text('comment')->nullable();
            $table->enum('role', ['passenger', 'driver']);
            $table->timestamps();

            $table->unique(['ride_id', 'rater_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ratings');
    }
};
