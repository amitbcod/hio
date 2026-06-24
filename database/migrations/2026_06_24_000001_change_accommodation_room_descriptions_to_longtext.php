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
            // Change room_description from text to longtext
            $table->longText('room_description')->nullable()->change();
            
            // Change short_description from string(250) to longtext
            $table->longText('short_description')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accommodation_rooms', function (Blueprint $table) {
            // Revert room_description back to text
            $table->text('room_description')->nullable()->change();
            
            // Revert short_description back to string(250)
            $table->string('short_description', 250)->nullable()->change();
        });
    }
};
