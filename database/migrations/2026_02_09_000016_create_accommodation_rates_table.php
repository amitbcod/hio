<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('accommodation_rates', function (Blueprint $table) {
            $table->id();
            $table->string('rate_id', 50)->unique();
            $table->unsignedBigInteger('accommodation_id')->required();
            $table->unsignedBigInteger('room_id')->nullable();
            $table->string('rate_name', 255)->required();
            $table->enum('rate_type', ['Standard', 'Seasonal', 'Promotion', 'Long Stay', 'Group'])->default('Standard');
            $table->date('valid_from')->required();
            $table->date('valid_to')->required();
            $table->decimal('base_rate', 10, 2)->required();
            $table->decimal('tax_amount', 10, 2)->nullable();
            $table->decimal('surcharge_amount', 10, 2)->nullable();
            $table->decimal('final_rate', 10, 2)->required();
            $table->string('currency', 3)->default('USD');
            $table->enum('meal_plan', ['Room Only', 'Breakfast', 'Half Board', 'Full Board', 'All Inclusive'])->default('Room Only');
            $table->integer('min_nights')->default(1);
            $table->integer('max_nights')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate()->useCurrent();
            $table->foreign('accommodation_id')->references('id')->on('accommodations')->onDelete('cascade');
            $table->index(['accommodation_id', 'valid_from', 'valid_to']);
            $table->index('accommodation_id');
            $table->index('room_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('accommodation_rates');
    }
};
