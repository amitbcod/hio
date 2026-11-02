<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const MPO_STAFF_FOREIGN_KEY = 'tba_mpo_staff_assigner_fk';

    public function up(): void
    {
        $tableName = 'transport_booking_assignments';
        $columnName = 'assigned_by_mpo_staff_user_id';

        if (! Schema::hasTable($tableName)) {
            return;
        }

        if (! Schema::hasColumn($tableName, $columnName)) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->unsignedBigInteger('assigned_by_mpo_staff_user_id')
                    ->nullable()
                    ->after('assigned_by');
            });
        }

        $foreignKeyExists = collect(Schema::getForeignKeys($tableName))
            ->contains(fn (array $foreignKey): bool => in_array($columnName, $foreignKey['columns'], true)
                && $foreignKey['foreign_table'] === 'mpo_staff_users'
                && $foreignKey['foreign_columns'] === ['id']);

        if ($foreignKeyExists) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table): void {
            $table->foreign('assigned_by_mpo_staff_user_id', self::MPO_STAFF_FOREIGN_KEY)
                ->references('id')
                ->on('mpo_staff_users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        $tableName = 'transport_booking_assignments';
        $columnName = 'assigned_by_mpo_staff_user_id';

        if (! Schema::hasTable($tableName) || ! Schema::hasColumn($tableName, $columnName)) {
            return;
        }

        $foreignKey = collect(Schema::getForeignKeys($tableName))
            ->first(fn (array $foreignKey): bool => in_array($columnName, $foreignKey['columns'], true)
                && $foreignKey['foreign_table'] === 'mpo_staff_users');

        if ($foreignKey) {
            Schema::table($tableName, function (Blueprint $table) use ($foreignKey): void {
                $table->dropForeign($foreignKey['name']);
            });
        }

        Schema::table($tableName, function (Blueprint $table) use ($columnName): void {
            $table->dropColumn($columnName);
        });
    }
};
