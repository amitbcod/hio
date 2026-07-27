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
        Schema::table('places', function (Blueprint $table) {
            // Change ENUM column to accept all 6 regions
            $table->enum('route_region', ['Airport', 'North', 'South', 'East', 'West', 'South East'])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('places', function (Blueprint $table) {
            // Revert to original ENUM values
            $table->enum('route_region', ['North', 'South', 'Airport'])->change();
        });
    }
};
