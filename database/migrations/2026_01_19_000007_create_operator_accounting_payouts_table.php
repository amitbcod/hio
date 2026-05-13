<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('operator_accounting_payouts', function (Blueprint $table) {
            $table->id();
            $table->string('beneficiary_id', 50);
            $table->string('bank_account_holder_name', 255);
            $table->string('bank_name', 255);
            $table->string('account_number', 50);
            $table->string('iban', 50)->nullable();
            $table->string('swift_code', 20)->nullable();
            $table->string('currency_preference', 3)->default('USD');
            $table->string('vat_number', 50)->nullable();
            $table->tinyInteger('vat_exempted')->default(0);
            $table->enum('commission_type', ['Fixed','Percentage']);
            $table->decimal('commission_value', 10, 2)->nullable();
            $table->integer('credit_limit_days')->nullable();
            $table->decimal('credit_limit_amount', 12, 2)->nullable();
            $table->decimal('credit_value', 12, 2)->nullable();
            $table->enum('payment_schedule', ['Monthly','On Request','Service Provided','Quarterly'])->default('Monthly');
            $table->decimal('outstanding_balance', 12, 2)->default(0.00);
            $table->enum('status', ['draft','active','inactive'])->default('draft');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate()->useCurrent();
            $table->unique('beneficiary_id');
            $table->index('beneficiary_id', 'idx_operator_accounting_payouts_beneficiary_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('operator_accounting_payouts');
    }
};
