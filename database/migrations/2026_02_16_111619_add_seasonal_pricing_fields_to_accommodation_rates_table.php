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
        Schema::table('accommodation_rates', function (Blueprint $table) {
            $table->decimal('extra_adult_rate', 10, 2)->nullable()->after('final_rate');
            $table->decimal('extra_bed_rate', 10, 2)->nullable()->after('extra_adult_rate');
            $table->decimal('children_rate', 10, 2)->nullable()->after('extra_bed_rate');
            $table->decimal('infant_rate', 10, 2)->nullable()->after('children_rate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accommodation_rates', function (Blueprint $table) {
            $table->dropColumn(['extra_adult_rate', 'extra_bed_rate', 'children_rate', 'infant_rate']);
        });
    }
};
