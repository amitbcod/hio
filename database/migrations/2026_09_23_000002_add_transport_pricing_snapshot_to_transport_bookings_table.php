<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transport_bookings', function (Blueprint $table) {
            $table->decimal('arrival_rate', 12, 2)->nullable()->after('price_per_person');
            $table->decimal('departure_rate', 12, 2)->nullable()->after('arrival_rate');
            $table->decimal('return_discount_percentage', 5, 2)->nullable()->after('departure_rate');
            $table->decimal('return_discount_amount', 12, 2)->nullable()->after('return_discount_percentage');
            $table->decimal('transport_price', 12, 2)->nullable()->after('return_discount_amount');
        });
    }

    public function down(): void
    {
        Schema::table('transport_bookings', function (Blueprint $table) {
            $table->dropColumn([
                'arrival_rate',
                'departure_rate',
                'return_discount_percentage',
                'return_discount_amount',
                'transport_price',
            ]);
        });
    }
};
