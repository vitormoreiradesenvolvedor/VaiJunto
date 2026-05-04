<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE ride_requests MODIFY COLUMN status ENUM('pending','accepted','rejected','cancelled','completed') NOT NULL DEFAULT 'pending'");
        } else {
            Schema::table('ride_requests', function (Blueprint $table) {
                $table->enum('status', ['pending', 'accepted', 'rejected', 'cancelled', 'completed'])->default('pending')->change();
            });
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE ride_requests MODIFY COLUMN status ENUM('pending','accepted','rejected','cancelled') NOT NULL DEFAULT 'pending'");
        } else {
            Schema::table('ride_requests', function (Blueprint $table) {
                $table->enum('status', ['pending', 'accepted', 'rejected', 'cancelled'])->default('pending')->change();
            });
        }
    }
};
