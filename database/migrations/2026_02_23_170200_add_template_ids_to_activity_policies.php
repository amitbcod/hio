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
        Schema::table('activity_policies', function (Blueprint $table) {
            $table->string('amendment_policy_template_id')->nullable()->after('amendment_policy_type');
            $table->string('cancellation_policy_template_id')->nullable()->after('cancellation_policy_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activity_policies', function (Blueprint $table) {
            $table->dropColumn(['amendment_policy_template_id', 'cancellation_policy_template_id']);
        });
    }
};
