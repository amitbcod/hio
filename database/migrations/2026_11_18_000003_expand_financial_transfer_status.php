<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('financial_settlement_transfers', function (Blueprint $table): void {
            $table->string('status', 24)->default('Recorded')->change();
        });
    }

    public function down(): void
    {
        Schema::table('financial_settlement_transfers', function (Blueprint $table): void {
            $table->string('status', 16)->default('Recorded')->change();
        });
    }
};
