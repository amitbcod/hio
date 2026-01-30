<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('operator_payout_history', function (Blueprint $table) {
            $table->id();
            $table->string('payout_id', 50);
            $table->string('beneficiary_id', 50);
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('total_commission', 12, 2)->default(0.00);
            $table->decimal('adjustments', 12, 2)->default(0.00)->nullable();
            $table->text('adjustment_reason')->nullable();
            $table->decimal('processing_fee', 10, 2)->default(0.00)->nullable();
            $table->decimal('payout_amount', 12, 2);
            $table->string('currency', 3)->default('MUR')->nullable();
            $table->enum('payout_method', ['Bank','Wallet','Check'])->default('Bank');
            $table->string('transaction_ref', 100)->nullable();
            $table->enum('status', ['Pending','Processing','Paid','Failed'])->default('Pending');
            $table->integer('processed_by')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate()->useCurrent();
            $table->unique('payout_id');
            $table->index('beneficiary_id', 'idx_operator_payout_history_beneficiary_id');
            $table->index('status', 'idx_operator_payout_history_status');
            $table->index(['period_start','period_end'], 'idx_period');
            $table->index('status', 'idx_payouts_status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('operator_payout_history');
    }
};
