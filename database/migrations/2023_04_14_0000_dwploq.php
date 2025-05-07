<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('deployment_rings', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('order');
            $table->string('version')->nullable();
            $table->boolean('auto_update')->default(false);
            $table->timestamps();
        });
        
        // Add deployment ring ID to stores table
        Schema::table('stores', function (Blueprint $table) {
            $table->foreignId('deployment_ring_id')->nullable()
                  ->constrained()->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropForeign(['deployment_ring_id']);
            $table->dropColumn('deployment_ring_id');
        });
        
        Schema::dropIfExists('deployment_rings');
    }
};