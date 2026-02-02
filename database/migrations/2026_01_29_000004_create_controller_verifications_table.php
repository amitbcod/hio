<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('controller_verifications', function (Blueprint $table) {
            $table->id();
            $table->string('token', 100)->unique();
            $table->unsignedBigInteger('business_id');
            $table->string('owner_email', 191);
            $table->string('owner_full_name')->nullable();
            $table->string('requester_operator_id', 50)->nullable();
            $table->enum('status', ['pending','approved','rejected','expired'])->default('pending');
            $table->unsignedBigInteger('accepted_by')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('token', 'idx_cv_token');
            $table->index('business_id', 'idx_cv_business_id');
            $table->index('owner_email', 'idx_cv_owner_email');
        });
    }

    public function down()
    {
        Schema::dropIfExists('controller_verifications');
    }
};
