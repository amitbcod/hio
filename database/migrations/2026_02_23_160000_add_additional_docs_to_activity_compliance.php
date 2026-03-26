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
        if (! Schema::hasTable('activity_compliance')) {
            return;
        }

        if (! Schema::hasColumn('activity_compliance', 'operational_assessment_doc')) {
            Schema::table('activity_compliance', function (Blueprint $table) {
                $table->string('operational_assessment_doc')->nullable()->after('insurance_file');
            });
        }

        if (! Schema::hasColumn('activity_compliance', 'emergency_plan_doc')) {
            Schema::table('activity_compliance', function (Blueprint $table) {
                $table->string('emergency_plan_doc')->nullable()->after('operational_assessment_doc');
            });
        }

        if (! Schema::hasColumn('activity_compliance', 'equipment_compliance_doc')) {
            Schema::table('activity_compliance', function (Blueprint $table) {
                $table->string('equipment_compliance_doc')->nullable()->after('emergency_plan_doc');
            });
        }

        if (! Schema::hasColumn('activity_compliance', 'equipment_registration_serial')) {
            Schema::table('activity_compliance', function (Blueprint $table) {
                $table->string('equipment_registration_serial')->nullable()->after('equipment_compliance_doc');
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
        if (! Schema::hasTable('activity_compliance')) {
            return;
        }

        $columns = [
            'operational_assessment_doc',
            'emergency_plan_doc',
            'equipment_compliance_doc',
            'equipment_registration_serial',
        ];

        $dropColumns = [];
        foreach ($columns as $column) {
            if (Schema::hasColumn('activity_compliance', $column)) {
                $dropColumns[] = $column;
            }
        }

        if (! empty($dropColumns)) {
            Schema::table('activity_compliance', function (Blueprint $table) use ($dropColumns) {
                $table->dropColumn($dropColumns);
            });
        }
    }
};
