<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('avatar')->nullable();
            $table->enum('role', ['passenger', 'driver', 'both'])->default('passenger');
            $table->boolean('is_active')->default(true);
            $table->integer('points_balance')->default(0);
            $table->double('last_lat', 10, 7)->nullable();
            $table->double('last_lng', 10, 7)->nullable();
            $table->timestamp('available_until')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
