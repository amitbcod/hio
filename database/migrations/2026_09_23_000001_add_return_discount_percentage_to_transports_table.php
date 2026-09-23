<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('transports', 'return_discount_percentage')) {
            Schema::table('transports', function (Blueprint $table) {
                $table->decimal('return_discount_percentage', 5, 2)->default(0)->after('routes_pricing');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('transports', 'return_discount_percentage')) {
            Schema::table('transports', function (Blueprint $table) {
                $table->dropColumn('return_discount_percentage');
            });
        }
    }
};
