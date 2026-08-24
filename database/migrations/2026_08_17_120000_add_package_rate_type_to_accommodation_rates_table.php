<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Alter the enum to include 'Package'. Using raw SQL for portability across existing installs.
        // Adjust the enum values to include Package while keeping the default.
        \DB::statement("ALTER TABLE accommodation_rates MODIFY rate_type ENUM('Standard','Seasonal','Promotion','Long Stay','Group','Package') NOT NULL DEFAULT 'Standard'");
    }

    public function down()
    {
        // Revert to previous enum (without Package)
        \DB::statement("ALTER TABLE accommodation_rates MODIFY rate_type ENUM('Standard','Seasonal','Promotion','Long Stay','Group') NOT NULL DEFAULT 'Standard'");
    }
};
