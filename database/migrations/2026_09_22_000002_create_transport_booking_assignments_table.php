<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('transport_booking_assignments')) {
            return;
        }

        Schema::create('transport_booking_assignments', function (Blueprint $table) {
            $table->id();
            // These references intentionally remain unsigned IDs without MySQL
            // foreign keys because legacy installations may use different
            // engines or integer definitions for the existing test tables.
            $table->unsignedBigInteger('transport_booking_id');
            $table->unsignedBigInteger('vehicle_id')->nullable();
            $table->unsignedBigInteger('driver_id')->nullable();
            $table->string('other_vehicle_name')->nullable();
            $table->string('other_vehicle_license_number')->nullable();
            $table->enum('status', ['Current', 'Replaced', 'Unassigned'])->default('Current');
            $table->text('reason')->nullable();
            $table->timestamp('assigned_at')->useCurrent();
            $table->timestamp('unassigned_at')->nullable();
            $table->unsignedBigInteger('assigned_by')->nullable();
            $table->timestamps();

            $table->index(['transport_booking_id', 'status']);
            $table->index('vehicle_id');
            $table->index('driver_id');
            $table->index('assigned_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transport_booking_assignments');
    }
};