<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            // Drop old Step 5 fields that have been moved to  activity_accounting table
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
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            // Restore the columns if migration is rolled back
            $table->text('bank_detail')->nullable();
            $table->string('vat_number')->nullable();
            $table->boolean('vat_exempted')->default(false);
            $table->string('agreement_name')->nullable();
            $table->string('commission_type')->nullable();
            $table->decimal('commission_value', 10, 2)->nullable();
            $table->string('currency_net', 10)->nullable();
            $table->string('tax_type')->nullable();
            $table->string('tax_charges_basis')->nullable();
            $table->string('tax_charges_type')->nullable();
            $table->decimal('tax_charges_value', 10, 2)->nullable();
            $table->string('tax_payment_collection')->nullable();
        });
    }
};
