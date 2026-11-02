<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mpo_collaboration_agreement_documents', function (Blueprint $table): void {
            $table->foreign('uploaded_by_admin_id', 'mpo_agreement_doc_uploader_fk')
                ->references('id')
                ->on('admin_users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('mpo_collaboration_agreement_documents', function (Blueprint $table): void {
            $table->dropForeign('mpo_agreement_doc_uploader_fk');
        });
    }
};
