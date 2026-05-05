<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('accommodation_bookings')) {
            Schema::table('accommodation_bookings', function (Blueprint $table) {
                if (!Schema::hasColumn('accommodation_bookings', 'is_guest')) {
                    $table->boolean('is_guest')->default(false)->after('booking_status');
                }

                if (!Schema::hasColumn('accommodation_bookings', 'guest_otp_token_id')) {
                    $table->unsignedBigInteger('guest_otp_token_id')->nullable()->after('is_guest');
                }
            });
        }

        if (Schema::hasTable('activity_bookings')) {
            Schema::table('activity_bookings', function (Blueprint $table) {
                if (!Schema::hasColumn('activity_bookings', 'is_guest')) {
                    $table->boolean('is_guest')->default(false)->after('booking_status');
                }

                if (!Schema::hasColumn('activity_bookings', 'guest_otp_token_id')) {
                    $table->unsignedBigInteger('guest_otp_token_id')->nullable()->after('is_guest');
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('accommodation_bookings')) {
            Schema::table('accommodation_bookings', function (Blueprint $table) {
                if (Schema::hasColumn('accommodation_bookings', 'is_guest')) {
                    $table->dropColumn('is_guest');
                }
                if (Schema::hasColumn('accommodation_bookings', 'guest_otp_token_id')) {
                    $table->dropColumn('guest_otp_token_id');
                }
            });
        }

        if (Schema::hasTable('activity_bookings')) {
            Schema::table('activity_bookings', function (Blueprint $table) {
                if (Schema::hasColumn('activity_bookings', 'is_guest')) {
                    $table->dropColumn('is_guest');
                }
                if (Schema::hasColumn('activity_bookings', 'guest_otp_token_id')) {
                    $table->dropColumn('guest_otp_token_id');
                }
            });
        }
    }
};
