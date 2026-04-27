<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Use raw SQL to avoid requiring doctrine/dbal for column modification.
        DB::statement('ALTER TABLE `accommodations` MODIFY COLUMN `og_description` TEXT NULL');
    }

    public function down()
    {
        DB::statement('ALTER TABLE `accommodations` MODIFY COLUMN `og_description` VARCHAR(255) NULL');
    }
};
