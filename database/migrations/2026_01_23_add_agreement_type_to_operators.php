<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('operators', function (Blueprint $table) {
            $table->enum('agreement_type', [
                'Listing Only',
                'OTO',
                'Widget Only',
                'OTO + Widget',
                'Full Service'
            ])->nullable()->after('user_type');
        });
    }

    public function down()
    {
        Schema::table('operators', function (Blueprint $table) {
            $table->dropColumn('agreement_type');
        });
    }
};
