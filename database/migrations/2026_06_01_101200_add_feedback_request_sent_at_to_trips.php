<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->timestamp('feedback_request_sent_at')->nullable()->after('end_date');
        });
    }

    public function down()
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->dropColumn('feedback_request_sent_at');
        });
    }
};
