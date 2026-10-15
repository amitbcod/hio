<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('accommodations', function (Blueprint $table) {
            $table->boolean('property_owned_by_operator')->default(false);
            $table->boolean('has_legal_business_identity')->default(false);
        });
    }

    public function down()
    {
        Schema::table('accommodations', function (Blueprint $table) {
            $table->dropColumn(['property_owned_by_operator', 'has_legal_business_identity']);
        });
    }
};
