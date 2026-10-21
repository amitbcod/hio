<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('countries') || ! Schema::hasColumn('countries', 'currency_code')) {
            return;
        }

        DB::table('countries')
            ->where('iso_code', 'MU')
            ->whereNull('currency_code')
            ->update(['currency_code' => 'MUR']);

        DB::table('countries')
            ->where('iso_code', 'IN')
            ->whereNull('currency_code')
            ->update(['currency_code' => 'INR']);
    }

    public function down(): void
    {
        if (! Schema::hasTable('countries') || ! Schema::hasColumn('countries', 'currency_code')) {
            return;
        }

        DB::table('countries')
            ->where('iso_code', 'MU')
            ->where('currency_code', 'MUR')
            ->update(['currency_code' => null]);

        DB::table('countries')
            ->where('iso_code', 'IN')
            ->where('currency_code', 'INR')
            ->update(['currency_code' => null]);
    }
};
