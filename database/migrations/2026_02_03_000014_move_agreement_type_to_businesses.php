<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Add agreement_type to businesses
        Schema::table('businesses', function (Blueprint $table) {
            $table->enum('agreement_type', [
                'Listing Only',
                'OTO',
                'Widget Only',
                'OTO + Widget',
                'Full Service'
            ])->nullable()->after('documents');
        });

        // Remove agreement_type from operators (if present)
        if (Schema::hasColumn('operators', 'agreement_type')) {
            Schema::table('operators', function (Blueprint $table) {
                $table->dropColumn('agreement_type');
            });
        }
    }

    public function down()
    {
        // Re-add agreement_type to operators
        Schema::table('operators', function (Blueprint $table) {
            $table->enum('agreement_type', [
                'Listing Only',
                'OTO',
                'Widget Only',
                'OTO + Widget',
                'Full Service'
            ])->nullable()->after('user_type');
        });

        // Remove agreement_type from businesses
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropColumn('agreement_type');
        });
    }
};