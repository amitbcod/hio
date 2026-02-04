<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('operator_service_operations', function (Blueprint $table) {
            $table->unsignedBigInteger('business_id')->nullable()->after('operator_id');
            $table->index('business_id', 'idx_operator_service_operations_business_id');
            $table->foreign('business_id')->references('id')->on('businesses')->onDelete('set null');
        });

        // No backfill performed — existing data are test data and will be ignored per project decision.
    }

    public function down()
    {
        Schema::table('operator_service_operations', function (Blueprint $table) {
            $table->dropForeign(['business_id']);
            $table->dropIndex('idx_operator_service_operations_business_id');
            $table->dropColumn('business_id');
        });
    }
};