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
            if (!Schema::hasColumn('accommodation_rates', 'pricing_setting')) {
                $table->enum('pricing_setting', ['Per Person/Night','Per Room/Night','Per Property/Night'])->default('Per Room/Night')->after('meal_plan');
            }
            if (!Schema::hasColumn('accommodation_rates', 'inclusions')) {
                $table->text('inclusions')->nullable()->after('pricing_setting');
            }
            if (!Schema::hasColumn('accommodation_rates', 'is_rate_plan')) {
                $table->boolean('is_rate_plan')->default(true)->after('inclusions');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accommodation_rates', function (Blueprint $table) {
            $cols = ['pricing_setting','inclusions','is_rate_plan'];
            foreach ($cols as $c) {
                if (Schema::hasColumn('accommodation_rates', $c)) {
                    $table->dropColumn($c);
                }
            }
        });
    }
};
