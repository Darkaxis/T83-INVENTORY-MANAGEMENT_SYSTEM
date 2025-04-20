<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pricing_tiers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('monthly_price', 10, 2);
            $table->decimal('annual_price', 10, 2)->nullable();
            $table->integer('product_limit')->nullable()->comment('Null or -1 means unlimited');
            $table->integer('user_limit')->nullable()->comment('Null or -1 means unlimited');
            $table->boolean('is_active')->default(true);
            $table->json('features_json')->nullable()->comment('JSON array of features included in this tier');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pricing_tiers');
    }
};