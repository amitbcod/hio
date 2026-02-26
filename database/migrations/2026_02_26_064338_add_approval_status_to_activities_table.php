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
            if (!Schema::hasColumn('activities', 'approval_status')) {
                $table->enum('approval_status', ['Draft', 'Pending', 'Approved', 'Rejected'])
                      ->default('Draft')
                      ->after('step12_seo_social');
            }
            
            if (!Schema::hasColumn('activities', 'approval_notes')) {
                $table->text('approval_notes')->nullable()->after('approval_status');
            }
            
            if (!Schema::hasColumn('activities', 'submitted_for_approval_at')) {
                $table->timestamp('submitted_for_approval_at')->nullable()->after('approval_notes');
            }
            
            if (!Schema::hasColumn('activities', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('submitted_for_approval_at');
            }
            
            if (!Schema::hasColumn('activities', 'approved_by')) {
                $table->unsignedBigInteger('approved_by')->nullable()->after('approved_at');
                $table->foreign('approved_by')->references('id')->on('admins')->onDelete('set null');
            }
            
            if (!Schema::hasColumn('activities', 'step13_publish')) {
                $table->boolean('step13_publish')->default(0)->after('step12_seo_social');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            if (Schema::hasColumn('activities', 'approved_by')) {
                $table->dropForeign(['approved_by']);
            }
            
            $columnsToCheck = ['step13_publish', 'approved_by', 'approved_at', 'submitted_for_approval_at', 'approval_notes', 'approval_status'];
            
            foreach ($columnsToCheck as $column) {
                if (Schema::hasColumn('activities', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
