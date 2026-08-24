<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up()
    {
        // Use raw SQL to avoid dependency on doctrine/dbal in this environment
        \DB::statement("ALTER TABLE activity_rates MODIFY valid_from DATE NULL");
        \DB::statement("ALTER TABLE activity_rates MODIFY valid_to DATE NULL");
    }

    public function down()
    {
        \DB::statement("ALTER TABLE activity_rates MODIFY valid_from DATE NOT NULL");
        \DB::statement("ALTER TABLE activity_rates MODIFY valid_to DATE NOT NULL");
    }
};
