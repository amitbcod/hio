<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_widgets', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('operator_id');
            $table->string('widget_token', 100)->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('operator_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_widgets');
    }
};
