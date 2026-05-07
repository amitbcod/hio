<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('accommodation_rooms', function (Blueprint $table) {
            if (!Schema::hasColumn('accommodation_rooms', 'max_person_capacity')) {
                $table->integer('max_person_capacity')->nullable()->after('infant_capacity')
                    ->comment('Maximum person occupancy for room category (adults + children + infants beyond first)');
            }
        });
    }

    public function down()
    {
        Schema::table('accommodation_rooms', function (Blueprint $table) {
            if (Schema::hasColumn('accommodation_rooms', 'max_person_capacity')) {
                $table->dropColumn('max_person_capacity');
            }
        });
    }
};
