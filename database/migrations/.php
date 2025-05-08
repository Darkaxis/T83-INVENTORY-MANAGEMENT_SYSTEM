<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Get the actual foreign key name for support_tickets.store_id
        $supportTicketFk = $this->getForeignKeyName('support_tickets', 'store_id');
        
        // Drop the existing constraint if it exists
        if ($supportTicketFk) {
            Schema::table('support_tickets', function (Blueprint $table) use ($supportTicketFk) {
                $table->dropForeign($supportTicketFk);
            });
        }
        
        // Add it back with cascade
        Schema::table('support_tickets', function (Blueprint $table) {
            $table->foreign('store_id')
                  ->references('id')
                  ->on('stores')
                  ->onDelete('cascade');
        });
        
        // Handle products table
        $productsFk = $this->getForeignKeyName('products', 'store_id');
        if ($productsFk) {
            Schema::table('products', function (Blueprint $table) use ($productsFk) {
                $table->dropForeign($productsFk);
            });
            
            Schema::table('products', function (Blueprint $table) {
                $table->foreign('store_id')
                      ->references('id')
                      ->on('stores')
                      ->onDelete('cascade');
            });
        }
        
        // Handle categories table
        $categoriesFk = $this->getForeignKeyName('categories', 'store_id');
        if ($categoriesFk) {
            Schema::table('categories', function (Blueprint $table) use ($categoriesFk) {
                $table->dropForeign($categoriesFk);
            });
            
            Schema::table('categories', function (Blueprint $table) {
                $table->foreign('store_id')
                      ->references('id')
                      ->on('stores')
                      ->onDelete('cascade');
            });
        }
        
        // Handle store_users table
        $storeUsersFk = $this->getForeignKeyName('store_users', 'store_id');
        if ($storeUsersFk) {
            Schema::table('store_users', function (Blueprint $table) use ($storeUsersFk) {
                $table->dropForeign($storeUsersFk);
            });
            
            Schema::table('store_users', function (Blueprint $table) {
                $table->foreign('store_id')
                      ->references('id')
                      ->on('stores')
                      ->onDelete('cascade');
            });
        }
    }

    /**
     * Get the actual foreign key name for a given table and column
     */
    private function getForeignKeyName($table, $column)
    {
        $foreignKey = DB::select(
            "SELECT CONSTRAINT_NAME 
             FROM information_schema.TABLE_CONSTRAINTS 
             WHERE TABLE_SCHEMA = DATABASE() 
             AND TABLE_NAME = ? 
             AND CONSTRAINT_TYPE = 'FOREIGN KEY'
             AND CONSTRAINT_NAME LIKE ?",
            [$table, "%{$column}%"]
        );
        
        return !empty($foreignKey) ? $foreignKey[0]->CONSTRAINT_NAME : null;
    }

    public function down(): void
    {
        // Return to original constraints if needed
    }
};