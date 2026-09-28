<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('transports') && Schema::hasColumn('transports', 'service_description')) {
            Schema::table('transports', function (Blueprint $table) {
                $table->text('service_description')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('transports') && Schema::hasColumn('transports', 'service_description')) {
            Schema::table('transports', function (Blueprint $table) {
                $table->string('service_description')->nullable()->change();
            });
        }
    }
};