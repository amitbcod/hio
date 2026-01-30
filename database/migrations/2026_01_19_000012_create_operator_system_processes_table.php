<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('operator_system_processes', function (Blueprint $table) {
            $table->id();
            $table->string('operator_id', 50);
            $table->enum('service_category', ['Accommodation','Activities','Transport','Services']);
            $table->enum('communication_preference', ['Email','Messaging System','WhatsApp','Phone'])->default('Email');
            $table->integer('assigned_operator_user_id')->nullable();
            $table->string('assigned_operator_name', 255)->nullable();
            $table->string('assigned_operator_role', 100)->nullable();
            $table->enum('status', ['draft','active','inactive'])->default('draft');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate()->useCurrent();
            $table->unique('operator_id');
            $table->index('operator_id', 'idx_operator_system_processes_operator_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('operator_system_processes');
    }
};
