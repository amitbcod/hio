<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_email_notifications', function (Blueprint $table): void {
            $table->id();
            $table->string('notification_key', 191)->unique();
            $table->foreignId('booking_ref_id')->nullable()->constrained('booking_refs')->nullOnDelete();
            $table->foreignId('trip_id')->nullable()->constrained('trips')->nullOnDelete();
            $table->string('audience', 24);
            $table->unsignedBigInteger('related_operator_id')->nullable();
            $table->json('service_booking_ids')->nullable();
            $table->string('recipient_email')->nullable();
            $table->string('status', 24)->default('pending');
            $table->unsignedInteger('attempts')->default(0);
            $table->timestamp('processing_at')->nullable();
            $table->timestamp('dispatched_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->text('failure_reason')->nullable();
            $table->timestamps();
            $table->index(['booking_ref_id', 'audience'], 'booking_email_ref_audience_idx');
            $table->index(['status', 'updated_at'], 'booking_email_status_updated_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_email_notifications');
    }
};
