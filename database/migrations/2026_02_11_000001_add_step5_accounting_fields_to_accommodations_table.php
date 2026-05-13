<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('accommodations', function (Blueprint $table) {
            // Banking details
            if (!Schema::hasColumn('accommodations', 'bank_details')) {
                $table->text('bank_details')->nullable()->after('insurance_expiration');
            }

            // VAT
            if (!Schema::hasColumn('accommodations', 'vat_number')) {
                $table->string('vat_number', 100)->nullable()->after('bank_details');
                $table->boolean('vat_exempted')->default(false)->after('vat_number');
            }

            // Tax fields
            if (!Schema::hasColumn('accommodations', 'tax_type')) {
                $table->enum('tax_type', ['Tourism', 'City Tax', 'None'])->nullable()->after('vat_exempted');
            }

            // Tax and service charges details
            if (!Schema::hasColumn('accommodations', 'tax_charges_type')) {
                $table->enum('tax_charges_type', ['Per Unit', 'Per Person', 'Per Adult'])->nullable()->after('tax_type');
                $table->enum('tax_charges_value_type', ['Amount', 'Percentage'])->nullable()->after('tax_charges_type');
                $table->decimal('tax_charges_value', 10, 2)->nullable()->after('tax_charges_value_type');
                $table->enum('tax_collection_method', ['Operator', 'MPO'])->nullable()->after('tax_charges_value');
            }

            // Agreement & Commission (readonly from agreement)
            if (!Schema::hasColumn('accommodations', 'hio_agreement_id')) {
                $table->unsignedBigInteger('hio_agreement_id')->nullable()->after('tax_collection_method');
                $table->string('agreement_name', 255)->nullable()->after('hio_agreement_id');
            }

            if (!Schema::hasColumn('accommodations', 'commission_type')) {
                $table->enum('commission_type', ['Amount', 'Percentage'])->nullable()->after('agreement_name');
                $table->decimal('commission_value', 10, 4)->nullable()->after('commission_type');
            }

            // Currency
            if (!Schema::hasColumn('accommodations', 'currency_code')) {
                $table->enum('currency_code', ['USD', 'EUR', 'USD', 'GBP', 'INR', 'AED', 'CNY'])->default('USD')->after('commission_value');
            }
        });
    }

    public function down()
    {
        Schema::table('accommodations', function (Blueprint $table) {
            $columns = [
                'bank_details', 'vat_number', 'vat_exempted', 'tax_type',
                'tax_charges_type', 'tax_charges_value_type', 'tax_charges_value',
                'tax_collection_method', 'hio_agreement_id', 'agreement_name',
                'commission_type', 'commission_value', 'currency_code'
            ];
            
            foreach ($columns as $col) {
                if (Schema::hasColumn('accommodations', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
