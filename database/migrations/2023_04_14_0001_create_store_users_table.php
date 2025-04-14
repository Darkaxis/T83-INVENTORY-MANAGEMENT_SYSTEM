<?php
// filepath: d:\WST\inventory-management-system\database\migrations\2023_04_14_create_store_users_table.php

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
        Schema::create('store_users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('store_id')->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('role')->default('user');
            $table->string('access_level')->default('limited');
            $table->string('store_password');
            $table->timestamps();
            
            // Each user can have only one role per store
            $table->unique(['store_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_users');
    }
};