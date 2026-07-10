<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('operators', function (Blueprint $table) {
            $table->json('transport_settings')->nullable()->after('steps_completed');
            $table->integer('transport_current_step')->default(1)->nullable()->after('transport_settings');
        });
    }

    public function down(): void
    {
        Schema::table('operators', function (Blueprint $table) {
            $table->dropColumn(['transport_settings', 'transport_current_step']);
        });
    }
};
