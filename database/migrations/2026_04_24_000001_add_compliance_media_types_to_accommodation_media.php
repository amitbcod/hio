<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // For MySQL: Modify the enum to add compliance types
        // Since we can't directly modify enums in Laravel migrations for MySQL,
        // we'll use a raw SQL statement
        
        Schema::table('accommodation_media', function (Blueprint $table) {
            // Get the current engine
            $connection = DB::connection()->getDriverName();
            
            if ($connection === 'mysql') {
                // Modify the enum to include compliance types
                DB::statement("ALTER TABLE accommodation_media MODIFY COLUMN media_type ENUM('hero','gallery','room','logo','video','compliance_permit','compliance_insurance','compliance_fire','compliance_health','compliance_other') DEFAULT 'gallery'");
            }
        });
    }

    public function down()
    {
        Schema::table('accommodation_media', function (Blueprint $table) {
            // Revert to original enum values
            DB::statement("ALTER TABLE accommodation_media MODIFY COLUMN media_type ENUM('hero','gallery','room','logo','video') DEFAULT 'gallery'");
        });
    }
};
