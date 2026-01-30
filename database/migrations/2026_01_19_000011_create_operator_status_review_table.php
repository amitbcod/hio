<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('operator_status_review', function (Blueprint $table) {
            $table->id();
            $table->string('operator_id', 50);
            $table->enum('account_status', ['Pending','Active','Suspended','Archived'])->default('Pending');
            $table->dateTime('last_approval_date')->nullable();
            $table->integer('profile_verified_by')->nullable();
            $table->dateTime('profile_verified_date')->nullable();
            $table->decimal('operator_rating', 3, 2)->default(0.00);
            $table->integer('testimonials_count')->default(0);
            $table->decimal('average_rating', 3, 2)->default(0.00);
            $table->date('renewal_reminder_date')->nullable();
            $table->integer('agreement_duration_days')->nullable();
            $table->date('agreement_expiry_date')->nullable();
            $table->integer('compliance_percentage')->default(0);
            $table->dateTime('last_compliance_check')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate()->useCurrent();
            $table->unique('operator_id');
            $table->index('operator_id', 'idx_operator_status_review_operator_id');
            $table->index('account_status', 'idx_account_status');
            $table->index('account_status', 'idx_status_review_account');
        });
    }

    public function down()
    {
        Schema::dropIfExists('operator_status_review');
    }
};
