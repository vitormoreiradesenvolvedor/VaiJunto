<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE fixed_routes MODIFY COLUMN status ENUM('active','paused','inactive','cancelled') NOT NULL DEFAULT 'active'");
        } else {
            DB::statement('ALTER TABLE fixed_routes DROP CONSTRAINT IF EXISTS fixed_routes_status_check');
            DB::statement("ALTER TABLE fixed_routes ALTER COLUMN status TYPE varchar(255)");
            DB::statement("ALTER TABLE fixed_routes ADD CONSTRAINT fixed_routes_status_check CHECK (status IN ('active', 'paused', 'inactive', 'cancelled'))");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE fixed_routes MODIFY COLUMN status ENUM('active','paused','inactive') NOT NULL DEFAULT 'active'");
        } else {
            DB::statement('ALTER TABLE fixed_routes DROP CONSTRAINT IF EXISTS fixed_routes_status_check');
            DB::statement("ALTER TABLE fixed_routes ALTER COLUMN status TYPE varchar(255)");
            DB::statement("ALTER TABLE fixed_routes ADD CONSTRAINT fixed_routes_status_check CHECK (status IN ('active', 'paused', 'inactive'))");
        }
    }
};
