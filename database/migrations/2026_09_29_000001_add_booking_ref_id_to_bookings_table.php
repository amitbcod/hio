<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->unsignedBigInteger('booking_ref_id')->nullable()->after('trip_id');
            $table->foreign('booking_ref_id')->references('id')->on('booking_refs')->nullOnDelete();
            $table->index(['trip_id', 'booking_ref_id']);
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['booking_ref_id']);
            $table->dropIndex(['trip_id', 'booking_ref_id']);
            $table->dropColumn('booking_ref_id');
        });
    }
};