<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('businesses', function (Blueprint $table) {
            $table->id();
            $table->string('business_id', 50)->unique();
            $table->string('legal_name', 255);
            $table->string('country', 100)->nullable();
            $table->string('registration_number', 100)->nullable();
            $table->enum('status', ['pending','active','suspended'])->default('pending');
            $table->string('primary_contact_email', 191)->nullable();
            $table->json('documents')->nullable();
            $table->timestamps();

            $table->index('business_id', 'idx_business_business_id');
            $table->index('status', 'idx_business_status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('businesses');
    }
};
