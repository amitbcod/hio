<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('modules')) {
            return;
        }

        $now = now();
        $modules = [];
        foreach (config('mpo_staff.registry', []) as $slug => $definition) {
            $modules[] = [
                'slug' => $slug,
                'name' => $definition['name'],
                'description' => $definition['description'],
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('modules')->insertOrIgnore($modules);
    }

    public function down(): void
    {
        // Keep canonical module records to avoid deleting permission mappings created after migration.
    }
};
