<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ride_requests', function (Blueprint $table) {
            $table->foreignId('fixed_route_id')
                  ->nullable()
                  ->after('trip_id')
                  ->constrained('fixed_routes')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('ride_requests', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\FixedRoute::class);
            $table->dropColumn('fixed_route_id');
        });
    }
};
