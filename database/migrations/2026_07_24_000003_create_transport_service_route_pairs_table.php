<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transport_service_route_pairs', function (Blueprint $table) {
            $table->id();
            $table->string('service_type');
            $table->string('route_from');
            $table->string('route_to');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['service_type', 'route_from', 'route_to'], 'tsrp_unique_pair');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transport_service_route_pairs');
    }
};
