<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transport_vehicles', function (Blueprint $table) {
            $table->date('license_expiry_date')->nullable()->after('registration_number');
            $table->date('insurance_expiry_date')->nullable()->after('license_expiry_date');
            $table->json('documents')->nullable()->after('policy_path');
        });
    }

    public function down(): void
    {
        Schema::table('transport_vehicles', function (Blueprint $table) {
            $table->dropColumn(['license_expiry_date', 'insurance_expiry_date', 'documents']);
        });
    }
};