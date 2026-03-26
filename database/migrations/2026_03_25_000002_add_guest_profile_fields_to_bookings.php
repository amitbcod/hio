<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = ['accommodation_bookings', 'activity_bookings'];

        foreach ($tables as $tableName) {
            if (!Schema::hasTable($tableName)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (!Schema::hasColumn($tableName, 'traveler_account_id')) {
                    $table->unsignedBigInteger('traveler_account_id')->nullable()->after('booking_reference');
                }

                if (!Schema::hasColumn($tableName, 'traveler_relation')) {
                    $table->enum('traveler_relation', ['self', 'spouse', 'child', 'friend', 'colleague', 'other'])->default('self')->after('traveler_account_id');
                }

                if (!Schema::hasColumn($tableName, 'traveler_first_name')) {
                    $table->string('traveler_first_name', 150)->nullable()->after('traveler_relation');
                }

                if (!Schema::hasColumn($tableName, 'traveler_middle_name')) {
                    $table->string('traveler_middle_name', 150)->nullable()->after('traveler_first_name');
                }

                if (!Schema::hasColumn($tableName, 'traveler_last_name')) {
                    $table->string('traveler_last_name', 150)->nullable()->after('traveler_middle_name');
                }

                if (!Schema::hasColumn($tableName, 'traveler_dob')) {
                    $table->date('traveler_dob')->nullable()->after('traveler_last_name');
                }

                if (!Schema::hasColumn($tableName, 'traveler_gender')) {
                    $table->enum('traveler_gender', ['male', 'female', 'non_binary', 'other'])->nullable()->after('traveler_dob');
                }

                if (!Schema::hasColumn($tableName, 'traveler_nationality')) {
                    $table->string('traveler_nationality', 100)->nullable()->after('traveler_gender');
                }

                if (!Schema::hasColumn($tableName, 'traveler_passport_number')) {
                    $table->string('traveler_passport_number', 100)->nullable()->after('traveler_nationality');
                }

                if (!Schema::hasColumn($tableName, 'traveler_notes')) {
                    $table->text('traveler_notes')->nullable()->after('traveler_passport_number');
                }
            });
        }
    }

    public function down(): void
    {
        $tables = ['accommodation_bookings', 'activity_bookings'];

        foreach ($tables as $tableName) {
            if (!Schema::hasTable($tableName)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                foreach ([
                    'traveler_account_id',
                    'traveler_relation',
                    'traveler_first_name',
                    'traveler_middle_name',
                    'traveler_last_name',
                    'traveler_dob',
                    'traveler_gender',
                    'traveler_nationality',
                    'traveler_passport_number',
                    'traveler_notes',
                ] as $col) {
                    if (Schema::hasColumn($tableName, $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
