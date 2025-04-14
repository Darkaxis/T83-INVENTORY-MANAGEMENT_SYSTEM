<?php
// filepath: d:\WST\inventory-management-system\database\migrations\2023_04_14_add_foreign_keys_to_store_users.php

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
        Schema::table('store_users', function (Blueprint $table) {
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('store_users', function (Blueprint $table) {
            $table->dropForeign(['store_id']);
            $table->dropForeign(['user_id']);
        });
    }
};