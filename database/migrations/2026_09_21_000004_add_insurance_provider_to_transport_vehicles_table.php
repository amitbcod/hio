<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transport_vehicles', function (Blueprint $table) {
            $table->string('insurance_provider')->nullable()->after('insurance_expiry_date');
        });
    }

    public function down(): void
    {
        Schema::table('transport_vehicles', function (Blueprint $table) {
            $table->dropColumn('insurance_provider');
        });
    }
};