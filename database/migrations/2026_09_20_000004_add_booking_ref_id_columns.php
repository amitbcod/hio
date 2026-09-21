<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accommodation_bookings', function (Blueprint $table) {
            $table->unsignedBigInteger('booking_ref_id')->nullable()->after('booking_reference');
            $table->foreign('booking_ref_id')->references('id')->on('booking_refs')->onDelete('set null');
        });

        Schema::table('activity_bookings', function (Blueprint $table) {
            $table->unsignedBigInteger('booking_ref_id')->nullable()->after('booking_reference');
            $table->foreign('booking_ref_id')->references('id')->on('booking_refs')->onDelete('set null');
        });

        Schema::table('transport_bookings', function (Blueprint $table) {
            $table->unsignedBigInteger('booking_ref_id')->nullable()->after('booking_reference');
            $table->foreign('booking_ref_id')->references('id')->on('booking_refs')->onDelete('set null');
        });

        Schema::table('payment_transactions', function (Blueprint $table) {
            $table->unsignedBigInteger('booking_ref_id')->nullable()->after('booking_id');
            $table->foreign('booking_ref_id')->references('id')->on('booking_refs')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('accommodation_bookings', function (Blueprint $table) {
            $table->dropForeign(['booking_ref_id']);
            $table->dropColumn('booking_ref_id');
        });

        Schema::table('activity_bookings', function (Blueprint $table) {
            $table->dropForeign(['booking_ref_id']);
            $table->dropColumn('booking_ref_id');
        });

        Schema::table('transport_bookings', function (Blueprint $table) {
            $table->dropForeign(['booking_ref_id']);
            $table->dropColumn('booking_ref_id');
        });

        Schema::table('payment_transactions', function (Blueprint $table) {
            $table->dropForeign(['booking_ref_id']);
            $table->dropColumn('booking_ref_id');
        });
    }
};
