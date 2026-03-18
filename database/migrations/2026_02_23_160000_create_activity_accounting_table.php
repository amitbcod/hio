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
        Schema::create('activity_accounting', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_id')->constrained('activities')->onDelete('cascade');
            
            // Bank Account Details
            $table->string('bank_account_holder_name')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('iban', 100)->nullable();
            $table->string('swift_code', 50)->nullable();
            
            // VAT Information
            $table->string('vat_number', 100)->nullable();
            $table->boolean('vat_exempted')->default(false);
            
            // Agreement & Commission (from OTO)
            $table->string('agreement_name')->nullable();
            $table->string('commission_type')->nullable(); // Amount or Percentage
            $table->decimal('commission_value', 10, 2)->nullable();
            $table->string('currency_net', 10)->nullable(); // MUR, EUR, USD
            
            // Tax Configuration
            $table->string('tax_type')->nullable(); // Tourism, City, Environmental, None
            $table->string('tax_charges_basis')->nullable(); // Per Activity, Per Person, Per Adult
            $table->string('tax_charges_type')->nullable(); // Amount or Percentage
            $table->decimal('tax_charges_value', 10, 2)->nullable();
            $table->string('tax_payment_collection')->nullable(); // Operator or MPO
            
            $table->timestamps();
            
            // Add unique constraint to ensure one accounting record per activity
            $table->unique('activity_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_accounting');
    }
};
