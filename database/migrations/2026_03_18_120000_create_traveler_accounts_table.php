<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('traveler_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('traveler_id', 50)->unique();
            $table->string('full_name', 150);
            $table->string('country', 100)->nullable();
            $table->string('email', 150)->unique();
            $table->string('mobile_phone', 25)->unique();
            $table->string('password_hash');
            $table->enum('verification_status', ['Unverified', 'Email', 'Phone', 'Both'])->default('Unverified');
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('phone_verified_at')->nullable();
            $table->timestamp('terms_accepted_at')->nullable();
            $table->string('terms_version', 30)->nullable();
            $table->timestamp('privacy_accepted_at')->nullable();
            $table->string('privacy_version', 30)->nullable();
            $table->boolean('marketing_opt_in')->default(false);
            $table->timestamp('last_login_at')->nullable();
            $table->rememberToken();
            $table->timestamps();

            $table->index('verification_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('traveler_accounts');
    }
};
