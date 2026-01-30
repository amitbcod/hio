<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('operator_service_operations', function (Blueprint $table) {
            $table->id();
            $table->string('operator_id', 50);
            $table->string('service_location', 255)->nullable();
            $table->tinyInteger('is_gps_location')->default(0);
            $table->string('gps_coordinates', 100)->nullable();
            $table->json('operating_areas')->nullable();
            $table->tinyInteger('is_nationwide')->default(0);
            $table->tinyInteger('has_pickup_dropoff')->default(0);
            $table->decimal('pickup_dropoff_surcharge', 10, 2)->nullable();
            $table->tinyInteger('pickup_dropoff_free')->default(0);
            $table->text('pickup_dropoff_details')->nullable();
            $table->string('emergency_contact_name', 255)->nullable();
            $table->string('emergency_contact_phone', 20)->nullable();
            $table->string('emergency_contact_email', 255)->nullable();
            $table->time('opening_time')->nullable();
            $table->time('closing_time')->nullable();
            $table->time('monday_open')->nullable();
            $table->time('monday_close')->nullable();
            $table->time('tuesday_open')->nullable();
            $table->time('tuesday_close')->nullable();
            $table->time('wednesday_open')->nullable();
            $table->time('wednesday_close')->nullable();
            $table->time('thursday_open')->nullable();
            $table->time('thursday_close')->nullable();
            $table->time('friday_open')->nullable();
            $table->time('friday_close')->nullable();
            $table->time('saturday_open')->nullable();
            $table->time('saturday_close')->nullable();
            $table->time('sunday_open')->nullable();
            $table->time('sunday_close')->nullable();
            $table->text('service_notes')->nullable();
            $table->json('operating_days')->nullable();
            $table->enum('status', ['draft','active','inactive'])->default('draft');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate()->useCurrent();
            $table->unique('operator_id');
            $table->index('operator_id', 'idx_operator_service_operations_operator_id');
            $table->index('operator_id', 'idx_service_ops_operator');
        });
    }

    public function down()
    {
        Schema::dropIfExists('operator_service_operations');
    }
};
