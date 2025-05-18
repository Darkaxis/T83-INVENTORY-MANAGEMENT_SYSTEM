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
        Schema::table('support_messages', function (Blueprint $table) {
            // Make the user_id nullable
            $table->unsignedBigInteger('user_id')->nullable()->change();
            
            // Add tenant_user_id column
            $table->unsignedBigInteger('tenant_user_id')->nullable()->after('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('support_messages', function (Blueprint $table) {
            $table->dropColumn('tenant_user_id');
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
        });
    }
};
