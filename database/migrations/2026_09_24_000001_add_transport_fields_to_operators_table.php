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
        Schema::table('operators', function (Blueprint $table) {
            $table->boolean('transport_same_as_business_address')->nullable()->default(null)->after('steps_completed');
            $table->string('transport_address')->nullable()->after('transport_same_as_business_address');
            $table->string('transport_region_location')->nullable()->after('transport_address');
            $table->string('transport_geolocation')->nullable()->after('transport_region_location');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('operators', function (Blueprint $table) {
            $table->dropColumn([
                'transport_same_as_business_address',
                'transport_address',
                'transport_region_location',
                'transport_geolocation',
            ]);
        });
    }
};
