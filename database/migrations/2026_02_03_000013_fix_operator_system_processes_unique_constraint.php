<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('operator_system_processes', function (Blueprint $table) {
            // Drop the unique constraint on operator_id (allow multiple operators to reference same process)
            $table->dropUnique('operator_system_processes_operator_id_unique');
            // Add unique constraint on business_id (only one system process per business, or per operator if no business)
            $table->unique(['business_id'], 'idx_operator_system_processes_business_id_unique');
        });
    }

    public function down()
    {
        Schema::table('operator_system_processes', function (Blueprint $table) {
            $table->dropUnique('idx_operator_system_processes_business_id_unique');
            $table->unique('operator_id');
        });
    }
};