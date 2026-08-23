<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Rename carts → old_carts
        if (Schema::hasTable('carts') && !Schema::hasTable('old_carts')) {
            Schema::rename('carts', 'old_carts');
        }

        // Rename cart_items → old_cart_items
        if (Schema::hasTable('cart_items') && !Schema::hasTable('old_cart_items')) {
            Schema::rename('cart_items', 'old_cart_items');
        }

        // OPTIONAL: Drop old tables (⚠️ do only after verification)
        // Schema::dropIfExists('old_carts');
        // Schema::dropIfExists('old_cart_items');
    }

    public function down(): void
    {
        // rollback rename
        if (Schema::hasTable('old_carts') && !Schema::hasTable('carts')) {
            Schema::rename('old_carts', 'carts');
        }

        if (Schema::hasTable('old_cart_items') && !Schema::hasTable('cart_items')) {
            Schema::rename('old_cart_items', 'cart_items');
        }
    }
};