<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('transport_routes', function (Blueprint $table) {
            if (!Schema::hasColumn('transport_routes', 'service_type')) {
                $table->string('service_type', 50)->nullable()->after('route_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transport_routes', function (Blueprint $table) {
            if (Schema::hasColumn('transport_routes', 'service_type')) {
                $table->dropColumn('service_type');
            }
        });
    }
};
