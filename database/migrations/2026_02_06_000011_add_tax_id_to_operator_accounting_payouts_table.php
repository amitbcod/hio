<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('operator_accounting_payouts', function (Blueprint $table) {
            $table->string('tax_id', 100)->nullable()->after('vat_number');
        });
    }

    public function down()
    {
        Schema::table('operator_accounting_payouts', function (Blueprint $table) {
            $table->dropColumn('tax_id');
        });
    }
};
