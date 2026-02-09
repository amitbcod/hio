<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('operator_system_processes', function (Blueprint $table) {
            if (Schema::hasColumn('operator_system_processes', 'service_category')) {
                $table->dropColumn('service_category');
            }
        });
    }

    public function down(): void
    {
        Schema::table('operator_system_processes', function (Blueprint $table) {
            $table->string('service_category', 255)->nullable();
        });
    }
};
