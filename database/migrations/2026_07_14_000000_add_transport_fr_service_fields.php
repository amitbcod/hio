<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('transports')) {
            Schema::table('transports', function (Blueprint $table) {
                if (!Schema::hasColumn('transports', 'long_description_fr')) {
                    $table->text('long_description_fr')->nullable()->after('service_description');
                }
                if (!Schema::hasColumn('transports', 'inclusions_fr')) {
                    $table->text('inclusions_fr')->nullable()->after('long_description_fr');
                }
                if (!Schema::hasColumn('transports', 'exclusions_fr')) {
                    $table->text('exclusions_fr')->nullable()->after('inclusions_fr');
                }
                if (!Schema::hasColumn('transports', 'pickup_instructions_fr')) {
                    $table->text('pickup_instructions_fr')->nullable()->after('exclusions_fr');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('transports')) {
            Schema::table('transports', function (Blueprint $table) {
                if (Schema::hasColumn('transports', 'pickup_instructions_fr')) {
                    $table->dropColumn('pickup_instructions_fr');
                }
                if (Schema::hasColumn('transports', 'exclusions_fr')) {
                    $table->dropColumn('exclusions_fr');
                }
                if (Schema::hasColumn('transports', 'inclusions_fr')) {
                    $table->dropColumn('inclusions_fr');
                }
                if (Schema::hasColumn('transports', 'long_description_fr')) {
                    $table->dropColumn('long_description_fr');
                }
            });
        }
    }
};
