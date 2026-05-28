<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE ride_requests MODIFY COLUMN status ENUM('pending','accepted','rejected','cancelled','completed') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE ride_requests MODIFY COLUMN status ENUM('pending','accepted','rejected','cancelled') NOT NULL DEFAULT 'pending'");
    }
};
