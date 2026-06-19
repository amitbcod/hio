<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('admin_users', function (Blueprint $table) {
            $table->string('business_name')->nullable()->after('role');
            $table->text('business_address')->nullable()->after('business_name');
            $table->string('phone_number', 100)->nullable()->after('email');
            $table->string('vat_number', 100)->nullable()->after('phone_number');
            $table->string('brn_number', 100)->nullable()->after('vat_number');
            $table->string('logo_path')->nullable()->after('brn_number');
        });
    }

    public function down()
    {
        Schema::table('admin_users', function (Blueprint $table) {
            $table->dropColumn(['business_name', 'business_address', 'phone_number', 'vat_number', 'brn_number', 'logo_path']);
        });
    }
};
