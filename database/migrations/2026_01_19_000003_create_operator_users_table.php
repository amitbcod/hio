<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('operator_users', function (Blueprint $table) {
            $table->id();
            $table->string('user_id', 50);
            $table->string('operator_id', 50);
            $table->string('full_name', 255);
            $table->string('email', 191);
            $table->string('mobile', 20)->nullable();
            $table->string('password_hash', 255);
            $table->dateTime('last_login')->nullable();
            $table->enum('role', ['Admin','Head of Department','Reservation Manager','Operational Manager','Finance Manager','Marketing Manager','Support Manager','Content Manager']);
            $table->json('access_rights')->nullable();
            $table->enum('status', ['Active','Inactive','Suspended'])->default('Active');
            $table->tinyInteger('account_reset_required')->default(0);
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate()->useCurrent();
            $table->integer('created_by')->nullable();
            $table->unique('user_id');
            $table->unique('email');
            $table->index('operator_id', 'idx_operator_users_operator_id');
            $table->index('email', 'idx_email');
            $table->index('role', 'idx_operator_users_role');
            $table->index('operator_id', 'idx_users_operator');
        });
    }

    public function down()
    {
        Schema::dropIfExists('operator_users');
    }
};
