<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('operator_drivers')) {
            // If table doesn't exist, create it with the expected columns
            Schema::create('operator_drivers', function (Blueprint $table) {
                $table->id();
                $table->string('operator_id', 50)->index();
                $table->unsignedBigInteger('business_id')->nullable()->index();

                $table->string('driver_id', 50)->unique();
                $table->string('driver_name', 150)->index();
                $table->string('driver_mobile_no', 30)->nullable();

                $table->string('driver_license_no', 60)->unique()->nullable();
                $table->date('license_expiry_date')->nullable();

                $table->enum('driver_status', ['Active', 'Off Duty', 'Sick Leave', 'Suspended', 'Inactive'])->default('Active')->index();
                $table->time('shift_start_time')->nullable();
                $table->time('shift_end_time')->nullable();
                $table->integer('driver_break_min')->nullable();

                $table->string('languages', 200)->nullable();
                $table->string('home_zone', 100)->nullable();
                $table->text('remarks')->nullable();

                $table->integer('total_trips')->default(0);
                $table->decimal('average_rating', 3, 2)->default(0);

                $table->timestamps();
                $table->softDeletes();
            });
            return;
        }

        // If table exists, add any missing columns individually
        Schema::table('operator_drivers', function (Blueprint $table) {
            // Note: Using Schema::hasColumn checks outside closure is necessary in some DB drivers,
            // but we'll be defensive and add columns only if missing using separate checks.
        });

        // Add columns conditionally
        if (!Schema::hasColumn('operator_drivers', 'driver_id')) {
            Schema::table('operator_drivers', function (Blueprint $table) {
                $table->string('driver_id', 50)->unique()->after('id');
            });
        }

        if (!Schema::hasColumn('operator_drivers', 'driver_name')) {
            Schema::table('operator_drivers', function (Blueprint $table) {
                $table->string('driver_name', 150)->nullable()->index()->after('driver_id');
            });
        }

        if (!Schema::hasColumn('operator_drivers', 'driver_mobile_no')) {
            Schema::table('operator_drivers', function (Blueprint $table) {
                $table->string('driver_mobile_no', 30)->nullable()->after('driver_name');
            });
        }

        if (!Schema::hasColumn('operator_drivers', 'driver_license_no')) {
            Schema::table('operator_drivers', function (Blueprint $table) {
                $table->string('driver_license_no', 60)->nullable()->unique()->after('driver_mobile_no');
            });
        }

        if (!Schema::hasColumn('operator_drivers', 'license_expiry_date')) {
            Schema::table('operator_drivers', function (Blueprint $table) {
                $table->date('license_expiry_date')->nullable()->after('driver_license_no');
            });
        }

        if (!Schema::hasColumn('operator_drivers', 'driver_status')) {
            Schema::table('operator_drivers', function (Blueprint $table) {
                $table->enum('driver_status', ['Active', 'Off Duty', 'Sick Leave', 'Suspended', 'Inactive'])->default('Active')->index()->after('license_expiry_date');
            });
        }

        if (!Schema::hasColumn('operator_drivers', 'shift_start_time')) {
            Schema::table('operator_drivers', function (Blueprint $table) {
                $table->time('shift_start_time')->nullable()->after('driver_status');
            });
        }

        if (!Schema::hasColumn('operator_drivers', 'shift_end_time')) {
            Schema::table('operator_drivers', function (Blueprint $table) {
                $table->time('shift_end_time')->nullable()->after('shift_start_time');
            });
        }

        if (!Schema::hasColumn('operator_drivers', 'driver_break_min')) {
            Schema::table('operator_drivers', function (Blueprint $table) {
                $table->integer('driver_break_min')->nullable()->after('shift_end_time');
            });
        }

        if (!Schema::hasColumn('operator_drivers', 'languages')) {
            Schema::table('operator_drivers', function (Blueprint $table) {
                $table->string('languages', 200)->nullable()->after('driver_break_min');
            });
        }

        if (!Schema::hasColumn('operator_drivers', 'home_zone')) {
            Schema::table('operator_drivers', function (Blueprint $table) {
                $table->string('home_zone', 100)->nullable()->after('languages');
            });
        }

        if (!Schema::hasColumn('operator_drivers', 'remarks')) {
            Schema::table('operator_drivers', function (Blueprint $table) {
                $table->text('remarks')->nullable()->after('home_zone');
            });
        }

        if (!Schema::hasColumn('operator_drivers', 'total_trips')) {
            Schema::table('operator_drivers', function (Blueprint $table) {
                $table->integer('total_trips')->default(0)->after('remarks');
            });
        }

        if (!Schema::hasColumn('operator_drivers', 'average_rating')) {
            Schema::table('operator_drivers', function (Blueprint $table) {
                $table->decimal('average_rating', 3, 2)->default(0)->after('total_trips');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('operator_drivers')) {
            return;
        }

        $columns = [
            'driver_id', 'driver_name', 'driver_mobile_no', 'driver_license_no', 'license_expiry_date',
            'driver_status', 'shift_start_time', 'shift_end_time', 'driver_break_min', 'languages', 'home_zone',
            'remarks', 'total_trips', 'average_rating'
        ];

        Schema::table('operator_drivers', function (Blueprint $table) use ($columns) {
            foreach ($columns as $col) {
                if (Schema::hasColumn('operator_drivers', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
