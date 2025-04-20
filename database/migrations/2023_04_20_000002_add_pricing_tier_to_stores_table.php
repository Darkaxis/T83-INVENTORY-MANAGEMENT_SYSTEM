<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->foreignId('pricing_tier_id')
                  ->nullable()
                  ->constrained()
                  ->nullOnDelete();
            $table->date('subscription_start_date')->nullable();
            $table->date('subscription_end_date')->nullable();
            $table->string('billing_cycle')->default('monthly')->comment('monthly, annual');
            $table->boolean('auto_renew')->default(true);
        });
    }

    public function down()
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropConstrainedForeignId('pricing_tier_id');
            $table->dropColumn('subscription_start_date');
            $table->dropColumn('subscription_end_date');
            $table->dropColumn('billing_cycle');
            $table->dropColumn('auto_renew');
        });
    }
};