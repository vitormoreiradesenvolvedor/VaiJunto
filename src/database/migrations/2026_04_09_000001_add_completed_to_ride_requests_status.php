<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE ride_requests MODIFY COLUMN status ENUM('pending','accepted','rejected','cancelled','completed') NOT NULL DEFAULT 'pending'");
        } else {
            DB::statement('ALTER TABLE ride_requests DROP CONSTRAINT IF EXISTS ride_requests_status_check');
            DB::statement("ALTER TABLE ride_requests ALTER COLUMN status TYPE varchar(255)");
            DB::statement("ALTER TABLE ride_requests ADD CONSTRAINT ride_requests_status_check CHECK (status IN ('pending', 'accepted', 'rejected', 'cancelled', 'completed'))");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE ride_requests MODIFY COLUMN status ENUM('pending','accepted','rejected','cancelled') NOT NULL DEFAULT 'pending'");
        } else {
            DB::statement('ALTER TABLE ride_requests DROP CONSTRAINT IF EXISTS ride_requests_status_check');
            DB::statement("ALTER TABLE ride_requests ALTER COLUMN status TYPE varchar(255)");
            DB::statement("ALTER TABLE ride_requests ADD CONSTRAINT ride_requests_status_check CHECK (status IN ('pending', 'accepted', 'rejected', 'cancelled'))");
        }
    }
};
