<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('operators', function (Blueprint $table) {
            if (!Schema::hasColumn('operators', 'agreement_type')) {
                $table->enum('agreement_type', ['Listing Only','OTO','Widget Only','OTO + Widget','Full Service'])->nullable()->after('business_legal_name');
            }
            if (!Schema::hasColumn('operators', 'booking_registration_type')) {
                $table->enum('booking_registration_type', ['Listing Only','OTO','Widget Only','OTO + Widget','Full Service'])->nullable()->after('agreement_type');
            }
        });
    }

    public function down()
    {
        Schema::table('operators', function (Blueprint $table) {
            if (Schema::hasColumn('operators', 'booking_registration_type')) {
                $table->dropColumn('booking_registration_type');
            }
            if (Schema::hasColumn('operators', 'agreement_type')) {
                $table->dropColumn('agreement_type');
            }
        });
    }
};
