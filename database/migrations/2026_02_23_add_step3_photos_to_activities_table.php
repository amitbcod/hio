<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('activities', function (Blueprint $table) {
            // Hero/Banner Image (Mandatory)
            $table->string('hero_banner_image')->nullable()->after('step2_management_communication');
            
            // Gallery Images (JSON array - min 3 images)
            $table->json('gallery_images')->nullable()->after('hero_banner_image');
            
            // Vehicle/Equipment Images (JSON array with vehicle type and image path)
            $table->json('vehicle_images')->nullable()->after('gallery_images');
            
            // Logo (Optional)
            $table->string('logo')->nullable()->after('vehicle_images');
            
            // Video (Optional)
            $table->string('video')->nullable()->after('logo');
            
            // Step 3 completion flag
            $table->tinyInteger('step3_photos_media')->default(0)->after('video');
        });
    }

    public function down()
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->dropColumn([
                'hero_banner_image',
                'gallery_images',
                'vehicle_images',
                'logo',
                'video',
                'step3_photos_media',
            ]);
        });
    }
};
