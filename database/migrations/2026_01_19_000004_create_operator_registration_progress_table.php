<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('operator_registration_progress', function (Blueprint $table) {
            $table->id();
            $table->string('operator_id', 50);
            $table->tinyInteger('step1_password')->default(0);
            $table->string('country_of_operation', 100)->nullable();
            $table->tinyInteger('step2_profile')->default(0);
            $table->tinyInteger('step3_legal')->default(0);
            $table->tinyInteger('step4_system_process')->default(0);
            $table->tinyInteger('step5_collaboration')->default(0);
            $table->tinyInteger('step6_users')->default(0);
            $table->tinyInteger('step7_accounting')->default(0);
            $table->tinyInteger('step8_operations')->default(0);
            $table->tinyInteger('step9_review')->default(0);
            $table->integer('current_step')->default(1)->nullable();
            $table->tinyInteger('registration_complete')->default(0);
            $table->dateTime('completion_date')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate()->useCurrent();
            $table->unique('operator_id');
            $table->index('operator_id', 'idx_operator_registration_progress_operator_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('operator_registration_progress');
    }
};
