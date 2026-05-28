<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('shared_carts', function (Blueprint $table) {
            $table->id();
            $table->string('owner_type', 50);
            $table->unsignedBigInteger('owner_id');
            $table->string('title');
            $table->string('token', 64)->unique();
            $table->json('items')->nullable();
            $table->string('status', 32)->default('Active');
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['owner_type', 'owner_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('shared_carts');
    }
};
