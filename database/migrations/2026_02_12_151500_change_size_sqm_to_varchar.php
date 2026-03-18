<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class ChangeSizeSqmToVarchar extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Use raw SQL to avoid needing doctrine/dbal
        DB::statement("ALTER TABLE `accommodation_rooms` MODIFY COLUMN `size_sqm` VARCHAR(50) NULL;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE `accommodation_rooms` MODIFY COLUMN `size_sqm` DECIMAL(6,2) NULL;");
    }
}
