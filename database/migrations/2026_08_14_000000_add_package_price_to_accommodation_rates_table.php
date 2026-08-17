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
            if (!Schema::hasColumn('accommodation_rates', 'package_price')) {
                $table->decimal('package_price', 10, 2)->nullable()->after('final_rate');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accommodation_rates', function (Blueprint $table) {
            if (Schema::hasColumn('accommodation_rates', 'package_price')) {
                $table->dropColumn('package_price');
            }
        });
    }
};
