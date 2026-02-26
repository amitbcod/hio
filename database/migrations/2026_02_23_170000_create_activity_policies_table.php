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
        Schema::create('activity_policies', function (Blueprint $table) {
            $table->id('policy_id');
            $table->unsignedBigInteger('activity_id')->unique();
            
            // Booking & Service
            $table->string('service_id')->nullable();
            $table->text('booking_window_rules')->nullable();
            
            // Policies
            $table->text('no_show_policy')->nullable();
            $table->text('amendment_policy')->nullable();
            $table->enum('amendment_policy_type', ['Custom', 'Template'])->default('Custom');
            $table->text('cancellation_policy')->nullable();
            $table->enum('cancellation_policy_type', ['Custom', 'Template'])->default('Custom');
            
            // Cancellation Penalties
            $table->enum('cancellation_penalties_enabled', ['Yes', 'No'])->default('No');
            $table->enum('cancellation_penalties_type', ['Person(s)', 'Percentage', 'Amount'])->nullable();
            $table->decimal('cancellation_penalties_value', 10, 2)->nullable();
            
            // Age Policies
            $table->integer('child_policy_age')->nullable();
            $table->integer('infant_policy_age')->nullable();
            
            // Safety & Health
            $table->text('safety_requirements')->nullable();
            $table->enum('health_requirements_type', ['None', 'Upload', 'Generate'])->default('None');
            $table->string('health_requirements_file')->nullable();
            
            $table->timestamps();
            
            $table->foreign('activity_id')->references('id')->on('activities')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_policies');
    }
};
