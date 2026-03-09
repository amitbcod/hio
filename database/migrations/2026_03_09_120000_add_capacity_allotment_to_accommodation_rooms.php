<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('accommodation_rooms', function (Blueprint $table) {
            // Capacity: Full capacity of the respective category
            $table->integer('max_capacity')->nullable()->after('quantity')->comment('Full capacity - max rooms/units available');
            
            // Allotment: Number of sellable rooms/units that can be allocated
            $table->integer('allotment')->nullable()->after('max_capacity')->comment('Number of sellable rooms/units');
        });
    }

    public function down()
    {
        Schema::table('accommodation_rooms', function (Blueprint $table) {
            $table->dropColumn(['max_capacity', 'allotment']);
        });
    }
};
