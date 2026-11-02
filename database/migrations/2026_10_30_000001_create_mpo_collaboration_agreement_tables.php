<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('admin_users', 'mpo_onboarded_at')) {
            Schema::table('admin_users', function (Blueprint $table): void {
                $table->timestamp('mpo_onboarded_at')->nullable();
            });
        }
        DB::table('admin_users')
            ->where('admin_type', 'mpo')
            ->where('status', 'active')
            ->whereNull('mpo_onboarded_at')
            ->update(['mpo_onboarded_at' => DB::raw('created_at')]);

        if (! Schema::hasTable('mpo_collaboration_agreements')) {
            Schema::create('mpo_collaboration_agreements', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('admin_user_id')->constrained('admin_users')->restrictOnDelete();
                $table->string('agreement_id', 32)->nullable()->unique();
                $table->string('agreement_type', 50)->default('mpo');
                $table->date('onboarding_date')->nullable();
                $table->date('start_date')->nullable();
                $table->string('commission_model', 32)->default('percentage');
                $table->decimal('commission_value', 7, 4)->nullable();
                $table->decimal('marketing_contribution_percent', 7, 4)->nullable();
                $table->string('marketing_contribution_payer', 16)->nullable();
                $table->string('status', 16)->default('Draft');
                $table->string('management_contact_name', 191)->nullable();
                $table->string('management_contact_email', 191)->nullable();
                $table->string('management_contact_phone', 20)->nullable();
                $table->string('management_contact_mobile', 20)->nullable();
                $table->string('accounting_contact_name', 191)->nullable();
                $table->string('accounting_contact_email', 191)->nullable();
                $table->string('accounting_contact_phone', 20)->nullable();
                $table->string('accounting_contact_mobile', 20)->nullable();
                $table->timestamps();
                $table->index(['admin_user_id', 'status'], 'mpo_agreement_owner_status_index');
                $table->index(['admin_user_id', 'start_date'], 'mpo_agreement_owner_start_index');
            });
        }

        if (! Schema::hasTable('mpo_collaboration_agreement_documents')) {
            Schema::create('mpo_collaboration_agreement_documents', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('agreement_id')->constrained('mpo_collaboration_agreements')->cascadeOnDelete();
                $table->string('storage_path', 255);
                $table->string('original_name', 255);
                $table->string('mime_type', 127);
                $table->unsignedInteger('size_bytes');
                $table->unsignedBigInteger('uploaded_by_admin_id')->nullable();
                $table->boolean('is_current')->default(true);
                $table->timestamps();
                $table->index(['agreement_id', 'is_current'], 'mpo_agreement_document_current_index');
            });
        }

        if (! Schema::hasTable('mpo_collaboration_agreement_events')) {
            Schema::create('mpo_collaboration_agreement_events', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('agreement_id')->constrained('mpo_collaboration_agreements')->cascadeOnDelete();
                $table->string('event_type', 32);
                $table->string('from_status', 16)->nullable();
                $table->string('to_status', 16)->nullable();
                $table->json('changes')->nullable();
                $table->string('reason', 1000)->nullable();
                $table->string('actor_type', 32);
                $table->unsignedBigInteger('actor_id')->nullable();
                $table->timestamps();
                $table->index(['agreement_id', 'created_at'], 'mpo_agreement_event_history_index');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('mpo_collaboration_agreement_events');
        Schema::dropIfExists('mpo_collaboration_agreement_documents');
        Schema::dropIfExists('mpo_collaboration_agreements');

        if (Schema::hasColumn('admin_users', 'mpo_onboarded_at')) {
            Schema::table('admin_users', function (Blueprint $table): void {
                $table->dropColumn('mpo_onboarded_at');
            });
        }
    }
};
