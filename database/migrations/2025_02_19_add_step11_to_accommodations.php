<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // public function up(): void
    // {
    //     Schema::table('accommodations', function (Blueprint $table) {
    //         // Add step 11 completion tracking
    //         if (!Schema::hasColumn('accommodations', 'step11_promotions_offers')) {
    //             $table->boolean('step11_promotions_offers')->default(false)->after('step10_inventory_allotment');
    //         }
    //     });
    // }

    public function up(): void
{
    if (!Schema::hasColumn('accommodations', 'step11_promotions_offers')) {
        Schema::table('accommodations', function (Blueprint $table) {
            $table->boolean('step11_promotions_offers')->default(false);
        });
    }
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accommodations', function (Blueprint $table) {
            if (Schema::hasColumn('accommodations', 'step11_promotions_offers')) {
                $table->dropColumn('step11_promotions_offers');
            }
        });
    }
};
