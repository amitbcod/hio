<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE activity_rates MODIFY valid_from DATE NULL");
            DB::statement("ALTER TABLE activity_rates MODIFY valid_to DATE NULL");
        }
    }

    public function down()
    {
        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE activity_rates MODIFY valid_from DATE NOT NULL");
            DB::statement("ALTER TABLE activity_rates MODIFY valid_to DATE NOT NULL");
        }
    }
};
