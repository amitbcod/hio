<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->string('bank_detail')->nullable()->after('step4_legal_compliance');
            $table->string('vat_number')->nullable()->after('bank_detail');
            $table->boolean('vat_exempted')->default(false)->after('vat_number');
            $table->string('agreement_name')->nullable()->after('vat_exempted');
            $table->string('commission_type')->nullable()->after('agreement_name');
            $table->decimal('commission_value', 10, 2)->nullable()->after('commission_type');
            $table->string('currency_net', 10)->nullable()->after('commission_value');
            $table->string('tax_type')->nullable()->after('currency_net');
            $table->string('tax_charges_basis')->nullable()->after('tax_type');
            $table->string('tax_charges_type')->nullable()->after('tax_charges_basis');
            $table->decimal('tax_charges_value', 10, 2)->nullable()->after('tax_charges_type');
            $table->string('tax_payment_collection')->nullable()->after('tax_charges_value');
            $table->tinyInteger('step5_accounting_transaction')->default(0)->after('tax_payment_collection');
        });
    }

    public function down()
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->dropColumn([
                'bank_detail',
                'vat_number',
                'vat_exempted',
                'agreement_name',
                'commission_type',
                'commission_value',
                'currency_net',
                'tax_type',
                'tax_charges_basis',
                'tax_charges_type',
                'tax_charges_value',
                'tax_payment_collection',
                'step5_accounting_transaction'
            ]);
        });
    }
};
