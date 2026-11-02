<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('activities')) {
            return;
        }

        // Only perform rename if the old column exists and the new one does not
        if (Schema::hasColumn('activities', 'region') && !Schema::hasColumn('activities', 'address')) {
            if (DB::connection()->getDriverName() === 'sqlite') {
                Schema::table('activities', function ($table): void {
                    $table->renameColumn('region', 'address');
                });
            } else {
                DB::statement("ALTER TABLE `activities` CHANGE `region` `address` VARCHAR(255) NULL;");
            }
        }
    }

    public function down()
    {
        if (!Schema::hasTable('activities')) {
            return;
        }

        if (Schema::hasColumn('activities', 'address') && !Schema::hasColumn('activities', 'region')) {
            if (DB::connection()->getDriverName() === 'sqlite') {
                Schema::table('activities', function ($table): void {
                    $table->renameColumn('address', 'region');
                });
            } else {
                DB::statement("ALTER TABLE `activities` CHANGE `address` `region` VARCHAR(255) NULL;");
            }
        }
    }
};
