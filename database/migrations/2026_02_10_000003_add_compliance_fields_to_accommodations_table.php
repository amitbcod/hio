<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('accommodations', function (Blueprint $table) {
            if (!Schema::hasColumn('accommodations', 'business_registration_number')) {
                $table->string('business_registration_number', 255)->nullable()->after('compliance_verified_by');
            }
            if (!Schema::hasColumn('accommodations', 'tourism_permit_number')) {
                $table->string('tourism_permit_number', 255)->nullable()->after('business_registration_number');
                $table->date('tourism_permit_expiration')->nullable()->after('tourism_permit_number');
            }
            if (!Schema::hasColumn('accommodations', 'public_liability_insurance_number')) {
                $table->string('public_liability_insurance_number', 255)->nullable()->after('tourism_permit_expiration');
                $table->date('insurance_expiration')->nullable()->after('public_liability_insurance_number');
            }
        });
    }

    public function down()
    {
        Schema::table('accommodations', function (Blueprint $table) {
            if (Schema::hasColumn('accommodations', 'business_registration_number')) {
                $table->dropColumn('business_registration_number');
            }
            if (Schema::hasColumn('accommodations', 'tourism_permit_number')) {
                $table->dropColumn(['tourism_permit_number', 'tourism_permit_expiration']);
            }
            if (Schema::hasColumn('accommodations', 'public_liability_insurance_number')) {
                $table->dropColumn(['public_liability_insurance_number', 'insurance_expiration']);
            }
        });
    }
};
