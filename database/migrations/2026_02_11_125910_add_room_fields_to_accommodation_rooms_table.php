<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('accommodation_rooms', function (Blueprint $table) {
            if (!Schema::hasColumn('accommodation_rooms', 'size_sqm')) {
                $table->decimal('size_sqm', 6, 2)->nullable()->after('room_type');
            }
            if (!Schema::hasColumn('accommodation_rooms', 'view')) {
                $table->string('view', 50)->nullable()->after('size_sqm');
            }
            if (!Schema::hasColumn('accommodation_rooms', 'smoking')) {
                $table->enum('smoking', ['Smoking','Non-smoking'])->nullable()->after('view');
            }
            if (!Schema::hasColumn('accommodation_rooms', 'short_description')) {
                $table->string('short_description', 250)->nullable()->after('room_description');
            }
            if (!Schema::hasColumn('accommodation_rooms', 'accessibility')) {
                $table->text('accessibility')->nullable()->after('amenities');
            }
            if (!Schema::hasColumn('accommodation_rooms', 'children_capacity')) {
                $table->integer('children_capacity')->default(0)->after('capacity');
            }
            if (!Schema::hasColumn('accommodation_rooms', 'infant_capacity')) {
                $table->integer('infant_capacity')->nullable()->after('children_capacity');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accommodation_rooms', function (Blueprint $table) {
            $cols = ['size_sqm','view','smoking','short_description','accessibility','children_capacity','infant_capacity'];
            foreach ($cols as $c) {
                if (Schema::hasColumn('accommodation_rooms', $c)) {
                    $table->dropColumn($c);
                }
            }
        });
    }
};
