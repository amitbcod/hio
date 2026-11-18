<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('countries')) {
            throw new \RuntimeException('The countries table must exist before importing country data.');
        }

        if (! Schema::hasColumn('countries', 'time_zone')) {
            Schema::table('countries', function (Blueprint $table): void {
                $table->text('time_zone')->nullable();
            });
        } else {
            Schema::table('countries', function (Blueprint $table): void {
                $table->text('time_zone')->nullable()->change();
            });
        }

        $dataPath = database_path('data/countries.json');
        $contents = file_get_contents($dataPath);
        if ($contents === false) {
            throw new \RuntimeException('Unable to read database/data/countries.json.');
        }

        $countries = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        if (! is_array($countries) || $countries === []) {
            throw new \RuntimeException('The country import data is empty or invalid.');
        }

        foreach (array_chunk($countries, 100) as $batch) {
            DB::table('countries')->upsert(
                $batch,
                ['iso_code'],
                ['name', 'currency_code', 'time_zone', 'is_active', 'vat_percentage', 'created_at', 'updated_at']
            );
        }
    }

    public function down(): void
    {
        // Country rows are shared reference data and may already be linked to application records.
    }
};
