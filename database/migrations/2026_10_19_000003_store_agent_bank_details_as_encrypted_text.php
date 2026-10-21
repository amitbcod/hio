<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('agent_operations') || ! Schema::hasColumn('agent_operations', 'bank_details')) {
            return;
        }

        Schema::table('agent_operations', function (Blueprint $table): void {
            $table->text('bank_details')->nullable()->change();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('agent_operations') || ! Schema::hasColumn('agent_operations', 'bank_details')) {
            return;
        }

        Schema::table('agent_operations', function (Blueprint $table): void {
            $table->json('bank_details')->nullable()->change();
        });
    }
};
