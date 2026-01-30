<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('operator_collaboration_agreements', function (Blueprint $table) {
            $table->id();
            $table->string('agreement_id', 50)->nullable();
            $table->string('operator_id', 50);
            $table->string('contact_management_name', 255);
            $table->string('contact_management_email', 255)->nullable();
            $table->string('contact_management_phone', 20)->nullable();
            $table->string('contact_management_mobile', 20)->nullable();
            $table->string('contact_accounting_name', 255)->nullable();
            $table->string('contact_accounting_email', 255)->nullable();
            $table->string('contact_accounting_phone', 20)->nullable();
            $table->string('contact_accounting_mobile', 20)->nullable();
            $table->enum('agreement_type', ['Listing Only','OTO','Widget Only','OTO + Widget','Full Service']);
            $table->string('agreement_file', 255);
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->string('commission_model', 50)->default('0');
            $table->decimal('commission_value', 10, 2);
            $table->string('payment_schedule', 100)->nullable();
            $table->decimal('marketing_contribution_percent', 5, 2)->nullable();
            $table->string('responsibilities_document', 255)->nullable();
            $table->enum('status', ['Draft','Active','Suspended','Terminated'])->default('Draft');
            $table->date('renewal_date')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate()->useCurrent();
            $table->unique('agreement_id');
            $table->index('operator_id', 'idx_operator_collaboration_agreements_operator_id');
            $table->index('status', 'idx_operator_collaboration_agreements_status');
            $table->index('status', 'idx_agreements_status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('operator_collaboration_agreements');
    }
};
