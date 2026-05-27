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
        Schema::table('activities', function (Blueprint $table) {
            $table->boolean('allow_adults')->default(true)->after('private_exclusive_option');
            $table->boolean('allow_children')->default(true)->after('allow_adults');
            $table->boolean('allow_infants')->default(true)->after('allow_children');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->dropColumn(['allow_adults', 'allow_children', 'allow_infants']);
        });
    }
};
