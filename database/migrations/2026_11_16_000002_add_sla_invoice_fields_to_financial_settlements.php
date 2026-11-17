<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('financial_settlements', function (Blueprint $table): void {
            $table->string('invoice_reference', 64)->nullable()->unique('fin_settle_invoice_ref_unique');
            $table->date('invoice_issued_on')->nullable();
            $table->date('invoice_due_on')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('financial_settlements', function (Blueprint $table): void {
            $table->dropUnique('fin_settle_invoice_ref_unique');
            $table->dropColumn(['invoice_reference', 'invoice_issued_on', 'invoice_due_on']);
        });
    }
};
