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
        Schema::table('activity_variants', function (Blueprint $table) {
            if (!Schema::hasColumn('activity_variants', 'variant_name_fr')) {
                $table->string('variant_name_fr')->nullable()->after('variant_name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activity_variants', function (Blueprint $table) {
            if (Schema::hasColumn('activity_variants', 'variant_name_fr')) {
                $table->dropColumn('variant_name_fr');
            }
        });
    }
};
