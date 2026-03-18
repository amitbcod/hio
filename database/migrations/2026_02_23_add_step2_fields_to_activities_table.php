<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('activities', function (Blueprint $table) {
            // Contact - Reservations (Mandatory)
            $table->string('reservation_contact_name')->nullable()->after('step1_basic');
            $table->string('reservation_contact_email')->nullable()->after('reservation_contact_name');
            $table->string('reservation_contact_phone')->nullable()->after('reservation_contact_email');
            $table->string('reservation_contact_mobile')->nullable()->after('reservation_contact_phone');
            
            // Contact - Accounting (Optional)
            $table->string('accounting_contact_name')->nullable()->after('reservation_contact_mobile');
            $table->string('accounting_contact_email')->nullable()->after('accounting_contact_name');
            $table->string('accounting_contact_phone')->nullable()->after('accounting_contact_email');
            $table->string('accounting_contact_mobile')->nullable()->after('accounting_contact_phone');
            
            // Contact - Management (Mandatory)
            $table->string('management_contact_name')->nullable()->after('accounting_contact_mobile');
            $table->string('management_contact_email')->nullable()->after('management_contact_name');
            $table->string('management_contact_phone')->nullable()->after('management_contact_email');
            $table->string('management_contact_mobile')->nullable()->after('management_contact_phone');
            
            // Contact - Operational Manager (Optional)
            $table->string('operational_manager_name')->nullable()->after('management_contact_mobile');
            $table->string('operational_manager_phone')->nullable()->after('operational_manager_name');
            
            // Booking Registration Type (System - Read-only)
            $table->string('booking_registration_type')->nullable()->after('operational_manager_phone');
            
            // Step 2 completion flag
            $table->tinyInteger('step2_management_communication')->default(0)->after('booking_registration_type');
        });
    }

    public function down()
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->dropColumn([
                'reservation_contact_name',
                'reservation_contact_email',
                'reservation_contact_phone',
                'reservation_contact_mobile',
                'accounting_contact_name',
                'accounting_contact_email',
                'accounting_contact_phone',
                'accounting_contact_mobile',
                'management_contact_name',
                'management_contact_email',
                'management_contact_phone',
                'management_contact_mobile',
                'operational_manager_name',
                'operational_manager_phone',
                'booking_registration_type',
                'step2_management_communication',
            ]);
        });
    }
};
