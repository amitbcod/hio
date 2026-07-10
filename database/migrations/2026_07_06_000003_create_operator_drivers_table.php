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
        Schema::create('operator_drivers', function (Blueprint $table) {
            $table->id();

            // Foreign keys
            $table->string('operator_id', 50)->index();
            $table->unsignedBigInteger('business_id')->nullable()->index();

            // Driver identification (master record)
            $table->string('driver_id', 50)->unique();
            $table->string('driver_name', 150)->index();
            $table->string('driver_mobile_no', 30)->nullable();

            // Driver Licence
            $table->string('driver_license_no', 60)->unique();
            $table->date('license_expiry_date');

            // Operational status & scheduling
            $table->enum('driver_status', ['Active', 'Off Duty', 'Sick Leave', 'Suspended', 'Inactive'])->default('Active')->index();
            $table->time('shift_start_time')->nullable();
            $table->time('shift_end_time')->nullable();
            $table->integer('driver_break_min')->nullable();

            // Operational metadata
            $table->string('languages', 200)->nullable();
            $table->string('home_zone', 100)->nullable();
            $table->text('remarks')->nullable();

            // Lightweight performance fields
            $table->integer('total_trips')->default(0);
            $table->decimal('average_rating', 3, 2)->default(0);

            // Timestamps
            $table->timestamps();
            $table->softDeletes();

            // Indexes & foreign keys
            $table->index(['operator_id', 'driver_status']);
            $table->foreign('operator_id')->references('operator_id')->on('operators')->onDelete('cascade');
            $table->foreign('business_id')->references('id')->on('businesses')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operator_drivers');
    }
};
