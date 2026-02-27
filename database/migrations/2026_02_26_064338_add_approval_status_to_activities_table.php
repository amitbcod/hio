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
                  ->default('Draft');
        }

        if (!Schema::hasColumn('activities', 'approval_notes')) {
            $table->text('approval_notes')->nullable();
        }

        if (!Schema::hasColumn('activities', 'submitted_for_approval_at')) {
            $table->timestamp('submitted_for_approval_at')->nullable();
        }

        if (!Schema::hasColumn('activities', 'approved_at')) {
            $table->timestamp('approved_at')->nullable();
        }

        if (!Schema::hasColumn('activities', 'approved_by')) {
            $table->foreignId('approved_by')
                  ->nullable()
                  ->constrained('admin_users')   // ✅ correct table
                  ->nullOnDelete();
        }

        if (!Schema::hasColumn('activities', 'step13_publish')) {
            $table->boolean('step13_publish')->default(false);
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
