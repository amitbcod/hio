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
        Schema::table('operator_collaboration_agreements', function (Blueprint $table) {
            // Modify the 'agreement_file' column to VARCHAR(255) and allow NULL
            $table->string('agreement_file', 255)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('operator_collaboration_agreements', function (Blueprint $table) {
            // Revert back to previous state if needed
            $table->string('agreement_file')->nullable(false)->change();
        });
    }
};
