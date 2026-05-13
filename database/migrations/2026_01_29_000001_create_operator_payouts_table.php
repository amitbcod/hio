<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('operator_payouts', function (Blueprint $table) {
            $table->id();
            $table->string('payout_id', 50)->unique(); // Unique payout batch id
            $table->string('beneficiary_id', 50)->index(); // operator id
            $table->string('beneficiary', 255); // operator name (denormalized)
            $table->string('period_covered', 50); // YYYY-MM or date-range
            $table->decimal('total_commission', 12, 2)->default(0.00);
            $table->decimal('adjustments', 12, 2)->nullable();
            $table->decimal('processing_fee', 12, 2)->default(0.00);
            $table->decimal('payout_amount', 12, 2)->default(0.00);
            $table->string('currency', 3)->default('USD');
            $table->enum('payout_method', ['Bank','Wallet'])->default('Bank');
            $table->string('transaction_ref', 255)->nullable();
            $table->enum('status', ['Pending','Processing','Paid','Failed'])->default('Pending');
            $table->string('processed_by', 255)->nullable(); // MPO user
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate()->useCurrent();
            $table->index('beneficiary_id', 'idx_payout_beneficiary');
            $table->index('status', 'idx_payout_status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('operator_payouts');
    }
};