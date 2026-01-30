<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('operators', function (Blueprint $table) {
            // link to businesses.id
            $table->unsignedBigInteger('business_id')->nullable()->after('operator_id');
            $table->index('business_id', 'idx_operators_business_id');
            $table->foreign('business_id')->references('id')->on('businesses')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('operators', function (Blueprint $table) {
            $table->dropForeign(['business_id']);
            $table->dropIndex('idx_operators_business_id');
            $table->dropColumn('business_id');
        });
    }
};
