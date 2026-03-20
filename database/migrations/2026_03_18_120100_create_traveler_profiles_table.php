<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('traveler_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('traveler_account_id')->unique()->constrained('traveler_accounts')->onDelete('cascade');
            $table->string('gender', 20)->nullable();
            $table->string('first_name', 100)->nullable();
            $table->string('middle_name', 100)->nullable();
            $table->string('last_name', 100)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('country', 100)->nullable();
            $table->string('nationality', 100)->nullable();
            $table->string('address_line_1', 255)->nullable();
            $table->string('address_line_2', 255)->nullable();
            $table->string('city_region', 150)->nullable();
            $table->string('emergency_contact_name', 150)->nullable();
            $table->string('emergency_contact_phone', 25)->nullable();
            $table->text('special_notes')->nullable();
            $table->string('preferred_language', 10)->default('EN');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('traveler_profiles');
    }
};
