<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('accommodations', function (Blueprint $table) {
            // Reservation mobile
            if (!Schema::hasColumn('accommodations', 'reservation_contact_mobile')) {
                $table->string('reservation_contact_mobile', 20)->nullable()->after('reservation_contact_phone');
            }

            // Accounting contact (optional)
            if (!Schema::hasColumn('accommodations', 'accounting_contact_name')) {
                $table->string('accounting_contact_name', 255)->nullable()->after('reservation_contact_mobile');
                $table->string('accounting_contact_email', 255)->nullable();
                $table->string('accounting_contact_phone', 20)->nullable();
                $table->string('accounting_contact_mobile', 20)->nullable();
            }

            // Management contact mobile (make mandatory in validation, but DB nullable to avoid breaking existing rows)
            if (!Schema::hasColumn('accommodations', 'management_contact_mobile')) {
                $table->string('management_contact_mobile', 20)->nullable()->after('management_contact_phone');
            }

            // Onsite / Front Desk
            if (!Schema::hasColumn('accommodations', 'onsite_department')) {
                $table->string('onsite_department', 255)->nullable()->after('management_contact_mobile');
                $table->string('onsite_phone', 20)->nullable();
            }

            // Booking registration / listing type (readonly in UI)
            if (!Schema::hasColumn('accommodations', 'booking_registration_type')) {
                $table->enum('booking_registration_type', ['Listing', 'OTO', 'MYP', 'Widget'])->nullable()->after('onsite_phone');
            }

            // Booking confirmation type
            if (!Schema::hasColumn('accommodations', 'booking_confirmation_type')) {
                $table->enum('booking_confirmation_type', ['Instant', 'On Request'])->nullable()->after('booking_registration_type');
            }
        });
    }

    public function down()
    {
        Schema::table('accommodations', function (Blueprint $table) {
            if (Schema::hasColumn('accommodations', 'reservation_contact_mobile')) {
                $table->dropColumn('reservation_contact_mobile');
            }
            if (Schema::hasColumn('accommodations', 'accounting_contact_name')) {
                $table->dropColumn(['accounting_contact_name', 'accounting_contact_email', 'accounting_contact_phone', 'accounting_contact_mobile']);
            }
            if (Schema::hasColumn('accommodations', 'management_contact_mobile')) {
                $table->dropColumn('management_contact_mobile');
            }
            if (Schema::hasColumn('accommodations', 'onsite_department')) {
                $table->dropColumn(['onsite_department', 'onsite_phone']);
            }
            if (Schema::hasColumn('accommodations', 'booking_registration_type')) {
                $table->dropColumn('booking_registration_type');
            }
            if (Schema::hasColumn('accommodations', 'booking_confirmation_type')) {
                $table->dropColumn('booking_confirmation_type');
            }
        });
    }
};