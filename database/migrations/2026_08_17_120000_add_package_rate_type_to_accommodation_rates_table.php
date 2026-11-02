<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE accommodation_rates MODIFY rate_type ENUM('Standard','Seasonal','Promotion','Long Stay','Group','Package') NOT NULL DEFAULT 'Standard'");
        }
    }

    public function down()
    {
        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE accommodation_rates MODIFY rate_type ENUM('Standard','Seasonal','Promotion','Long Stay','Group') NOT NULL DEFAULT 'Standard'");
        }
    }
};
