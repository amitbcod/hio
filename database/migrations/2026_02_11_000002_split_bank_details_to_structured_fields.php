<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('accommodations', function (Blueprint $table) {
            // Replace bank_details with structured fields
            if (Schema::hasColumn('accommodations', 'bank_details')) {
                $table->dropColumn('bank_details');
            }

            if (!Schema::hasColumn('accommodations', 'bank_account_holder_name')) {
                $table->string('bank_account_holder_name', 255)->nullable()->after('insurance_expiration');
                $table->string('bank_name', 255)->nullable()->after('bank_account_holder_name');
                $table->string('account_number', 100)->nullable()->after('bank_name');
                $table->string('iban', 100)->nullable()->after('account_number');
                $table->string('swift_code', 50)->nullable()->after('iban');
            }
        });
    }

    public function down()
    {
        Schema::table('accommodations', function (Blueprint $table) {
            $cols = ['bank_account_holder_name', 'bank_name', 'account_number', 'iban', 'swift_code'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('accommodations', $col)) {
                    $table->dropColumn($col);
                }
            }
            if (!Schema::hasColumn('accommodations', 'bank_details')) {
                $table->text('bank_details')->nullable()->after('insurance_expiration');
            }
        });
    }
};
