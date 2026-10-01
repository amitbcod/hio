<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('booking_refs', 'idempotency_key')) {
            Schema::table('booking_refs', function (Blueprint $table) {
                $table->uuid('idempotency_key')->nullable()->unique();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('booking_refs', 'idempotency_key')) {
            Schema::table('booking_refs', function (Blueprint $table) {
                $table->dropUnique(['idempotency_key']);
                $table->dropColumn('idempotency_key');
            });
        }
    }
};