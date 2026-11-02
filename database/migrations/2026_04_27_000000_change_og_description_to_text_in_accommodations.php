<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            return;
        }

        DB::statement('ALTER TABLE `accommodations` MODIFY COLUMN `og_description` TEXT NULL');
    }

    public function down()
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            return;
        }

        DB::statement('ALTER TABLE `accommodations` MODIFY COLUMN `og_description` VARCHAR(255) NULL');
    }
};
