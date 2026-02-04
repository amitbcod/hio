<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('role_module_permissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('role_id')->nullable()->index();
            $table->unsignedBigInteger('module_id')->index();
            $table->unsignedBigInteger('business_id')->nullable()->index();
            $table->boolean('can_read')->default(false);
            $table->boolean('can_create')->default(false);
            $table->boolean('can_update')->default(false);
            $table->boolean('can_approve')->default(false);
            $table->boolean('can_publish')->default(false);
            $table->timestamps();

            $table->foreign('module_id')->references('id')->on('modules')->onDelete('cascade');
            // role_id may reference roles.id if using spatie roles table - leave nullable to allow global role-less mappings
        });
    }

    public function down()
    {
        Schema::dropIfExists('role_module_permissions');
    }
};
