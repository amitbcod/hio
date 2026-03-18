<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('accommodations', function (Blueprint $table) {
            if (!Schema::hasColumn('accommodations', 'seo_title')) {
                $table->string('seo_title')->nullable()->after('status');
            }
            if (!Schema::hasColumn('accommodations', 'seo_description')) {
                $table->string('seo_description')->nullable()->after('seo_title');
            }
            if (!Schema::hasColumn('accommodations', 'keywords_tags')) {
                $table->text('keywords_tags')->nullable()->after('seo_description');
            }
            if (!Schema::hasColumn('accommodations', 'og_title')) {
                $table->string('og_title')->nullable()->after('keywords_tags');
            }
            if (!Schema::hasColumn('accommodations', 'og_description')) {
                $table->string('og_description')->nullable()->after('og_title');
            }
            if (!Schema::hasColumn('accommodations', 'og_image')) {
                $table->string('og_image')->nullable()->after('og_description');
            }
            if (!Schema::hasColumn('accommodations', 'step12_review')) {
                $table->tinyInteger('step12_review')->default(0)->after('step11_promotions_offers');
            }
        });
    }

    public function down()
    {
        Schema::table('accommodations', function (Blueprint $table) {
            if (Schema::hasColumn('accommodations', 'seo_title')) {
                $table->dropColumn('seo_title');
            }
            if (Schema::hasColumn('accommodations', 'seo_description')) {
                $table->dropColumn('seo_description');
            }
            if (Schema::hasColumn('accommodations', 'keywords_tags')) {
                $table->dropColumn('keywords_tags');
            }
            if (Schema::hasColumn('accommodations', 'og_title')) {
                $table->dropColumn('og_title');
            }
            if (Schema::hasColumn('accommodations', 'og_description')) {
                $table->dropColumn('og_description');
            }
            if (Schema::hasColumn('accommodations', 'og_image')) {
                $table->dropColumn('og_image');
            }
            if (Schema::hasColumn('accommodations', 'step12_review')) {
                $table->dropColumn('step12_review');
            }
        });
    }
};
