<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('operator_drivers')) {
            return;
        }

        $db = DB::getDatabaseName();

        $cols = DB::select(
            "SELECT COLUMN_NAME, COLUMN_TYPE, DATA_TYPE
             FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'operator_drivers'
               AND IS_NULLABLE = 'NO' AND COLUMN_DEFAULT IS NULL",
            [$db]
        );

        $skip = ['id', 'created_at', 'updated_at', 'deleted_at'];

        foreach ($cols as $col) {
            $name = $col->COLUMN_NAME;
            if (in_array($name, $skip)) {
                continue;
            }

            $type = $col->COLUMN_TYPE; // includes length, e.g. varchar(255)

            // Best-effort: make column nullable. For text/varchar types set default '', for numeric set default 0.
            try {
                if (stripos($type, 'char') !== false || stripos($type, 'text') !== false || stripos($type, 'blob') !== false) {
                    DB::statement("ALTER TABLE `operator_drivers` MODIFY COLUMN `{$name}` {$type} NULL DEFAULT ''");
                } elseif (preg_match('/int|decimal|double|float|numeric|year|bit/i', $type)) {
                    DB::statement("ALTER TABLE `operator_drivers` MODIFY COLUMN `{$name}` {$type} NULL DEFAULT 0");
                } else {
                    // For date/time/timestamp and other types, just allow NULL without default
                    DB::statement("ALTER TABLE `operator_drivers` MODIFY COLUMN `{$name}` {$type} NULL");
                }
            } catch (\Exception $e) {
                // ignore individual failures; migration is best-effort to stabilise dev DBs
                continue;
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverting can be dangerous; no-op.
    }
};
