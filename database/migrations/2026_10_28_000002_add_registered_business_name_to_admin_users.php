<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('admin_users', 'registered_business_name')) {
            Schema::table('admin_users', function (Blueprint $table): void {
                $table->string('registered_business_name', 191)->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('admin_users', 'registered_business_name')) {
            Schema::table('admin_users', function (Blueprint $table): void {
                $table->dropColumn('registered_business_name');
            });
        }
    }
};
