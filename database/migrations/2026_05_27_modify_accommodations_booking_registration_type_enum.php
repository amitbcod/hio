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

        DB::statement("ALTER TABLE `accommodations` MODIFY `booking_registration_type` ENUM('Listing','Listing Only','OTO','Widget','Widget Only','MYP','OTO + Widget','Full Service') NULL;");
    }

    public function down()
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            return;
        }

        DB::statement("ALTER TABLE `accommodations` MODIFY `booking_registration_type` ENUM('Listing','OTO','MYP','Widget') NULL;");
    }
};
