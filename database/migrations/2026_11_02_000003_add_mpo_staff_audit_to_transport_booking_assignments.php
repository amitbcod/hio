<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('transport_booking_assignments')
            || Schema::hasColumn('transport_booking_assignments', 'assigned_by_mpo_staff_user_id')) {
            return;
        }

        Schema::table('transport_booking_assignments', function (Blueprint $table): void {
            $table->foreignId('assigned_by_mpo_staff_user_id')
                ->nullable()
                ->after('assigned_by')
                ->constrained('mpo_staff_users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('transport_booking_assignments')
            || ! Schema::hasColumn('transport_booking_assignments', 'assigned_by_mpo_staff_user_id')) {
            return;
        }

        Schema::table('transport_booking_assignments', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('assigned_by_mpo_staff_user_id');
        });
    }
};
