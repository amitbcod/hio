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
        Schema::table('activity_compliance', function (Blueprint $table) {
            $table->string('operational_assessment_doc')->nullable()->after('insurance_file');
            $table->string('emergency_plan_doc')->nullable()->after('operational_assessment_doc');
            $table->string('equipment_compliance_doc')->nullable()->after('emergency_plan_doc');
            $table->string('equipment_registration_serial')->nullable()->after('equipment_compliance_doc');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('activity_compliance', function (Blueprint $table) {
            $table->dropColumn([
                'operational_assessment_doc',
                'emergency_plan_doc',
                'equipment_compliance_doc',
                'equipment_registration_serial'
            ]);
        });
    }
};
