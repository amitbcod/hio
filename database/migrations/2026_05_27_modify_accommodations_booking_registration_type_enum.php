<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Modify enum values to match operator agreement types
        // Allow both old and new values during migration to avoid blocking existing rows.
        DB::statement("ALTER TABLE `accommodations` MODIFY `booking_registration_type` ENUM('Listing','Listing Only','OTO','Widget','Widget Only','MYP','OTO + Widget','Full Service') NULL;");
    }

    public function down()
    {
        // Revert to previous enum values
        DB::statement("ALTER TABLE `accommodations` MODIFY `booking_registration_type` ENUM('Listing','OTO','MYP','Widget') NULL;");
    }
};
