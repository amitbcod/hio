<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('policy_templates', function (Blueprint $table) {
            if (!Schema::hasColumn('policy_templates', 'content_fr')) {
                $table->longText('content_fr')->nullable()->after('content');
            }
        });
    }

    public function down()
    {
        Schema::table('policy_templates', function (Blueprint $table) {
            if (Schema::hasColumn('policy_templates', 'content_fr')) {
                $table->dropColumn('content_fr');
            }
        });
    }
};
