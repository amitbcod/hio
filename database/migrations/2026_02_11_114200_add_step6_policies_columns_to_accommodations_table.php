<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('accommodations', function (Blueprint $table) {
            // Check-in/Check-out
            $table->time('checkin_time')->nullable();
            $table->time('checkout_time')->nullable();
            $table->text('checkin_checkout_rules')->nullable();
            
            // Booking Window
            $table->text('booking_window_rules')->nullable();
            
            // Amendment Policy
            $table->enum('amendment_policy_type', ['custom', 'template'])->default('custom')->nullable();
            $table->longText('amendment_policy')->nullable();
            $table->string('amendment_policy_template_id', 100)->nullable();
            
            // Cancellation Policy
            $table->enum('cancellation_policy_type', ['custom', 'template'])->default('custom')->nullable();
            $table->longText('cancellation_policy')->nullable();
            $table->string('cancellation_policy_template_id', 100)->nullable();
            
            // Cancellation Penalties
            $table->boolean('cancellation_penalties_enabled')->default(false);
            $table->enum('cancellation_penalty_type', ['Night', 'Percentage', 'Amount'])->nullable();
            $table->decimal('cancellation_penalty_value', 10, 2)->nullable();
            
            // Security Deposit Policy
            $table->enum('security_deposit_policy_type', ['custom', 'template'])->default('custom')->nullable();
            $table->longText('security_deposit_policy')->nullable();
            $table->string('security_deposit_policy_template_id', 100)->nullable();
            
            // Deposit Settings
            $table->boolean('deposit_required')->default(false);
            $table->enum('deposit_type', ['Night', 'Percentage', 'Amount'])->nullable();
            $table->decimal('deposit_value', 10, 2)->nullable();
            
            // Child & Infant Policies
            $table->unsignedInteger('child_max_age')->nullable();
            $table->unsignedInteger('infant_max_age')->nullable();
            
            // House Rules
            $table->enum('house_rules_type', ['custom', 'template'])->default('custom')->nullable();
            $table->longText('house_rules')->nullable();
            $table->string('house_rules_template_id', 100)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accommodations', function (Blueprint $table) {
            $table->dropColumn([
                'checkin_time',
                'checkout_time',
                'checkin_checkout_rules',
                'booking_window_rules',
                'amendment_policy_type',
                'amendment_policy',
                'amendment_policy_template_id',
                'cancellation_policy_type',
                'cancellation_policy',
                'cancellation_policy_template_id',
                'cancellation_penalties_enabled',
                'cancellation_penalty_type',
                'cancellation_penalty_value',
                'security_deposit_policy_type',
                'security_deposit_policy',
                'security_deposit_policy_template_id',
                'deposit_required',
                'deposit_type',
                'deposit_value',
                'child_max_age',
                'infant_max_age',
                'house_rules_type',
                'house_rules',
                'house_rules_template_id',
            ]);
        });
    }
};
