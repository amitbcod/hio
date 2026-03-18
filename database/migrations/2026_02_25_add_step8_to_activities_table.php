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
            if (!Schema::hasColumn('activities', 'step8_scheduling_timeslots')) {
                $table->boolean('step8_scheduling_timeslots')->default(0)->after('step7_variants_equipment');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            if (Schema::hasColumn('activities', 'step8_scheduling_timeslots')) {
                $table->dropColumn('step8_scheduling_timeslots');
            }
        });
    }
};
