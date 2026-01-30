<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('operator_role_access_mapping', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->string('role', 100);
            $table->enum('module', ['Account','Profile','Compliance','Users','Reservation','Accounting','Operations','Marketing','Content','Support','Feedback']);
            $table->tinyInteger('can_read')->default(1);
            $table->tinyInteger('can_create')->default(0);
            $table->tinyInteger('can_update')->default(0);
            $table->tinyInteger('can_approve')->default(0);
            $table->tinyInteger('can_publish')->default(0);
            $table->string('capacity_level', 100)->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate()->useCurrent();
            $table->unique(['user_id','module'], 'unique_user_module');
            $table->index('role', 'idx_operator_role_access_mapping_role');
        });
    }

    public function down()
    {
        Schema::dropIfExists('operator_role_access_mapping');
    }
};
