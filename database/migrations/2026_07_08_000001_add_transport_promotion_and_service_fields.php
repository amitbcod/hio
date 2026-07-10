<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('transports', function (Blueprint $table) {
            $table->json('promotions_offers')->nullable()->after('service_description');
            $table->longText('long_description')->nullable()->after('promotions_offers');
            $table->longText('inclusions')->nullable()->after('long_description');
            $table->longText('exclusions')->nullable()->after('inclusions');
            $table->longText('pickup_instructions')->nullable()->after('exclusions');
            $table->boolean('step5_promotions_offers')->default(false)->after('step4_compliance');
            $table->boolean('step6_service_description')->default(false)->after('step5_promotions_offers');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transports', function (Blueprint $table) {
            $table->dropColumn([
                'promotions_offers',
                'long_description',
                'inclusions',
                'exclusions',
                'pickup_instructions',
                'step5_promotions_offers',
                'step6_service_description',
            ]);
        });
    }
};
