<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('booking_items')) {
            Schema::drop('booking_items');
        }
        // Drop old tables safely
        if (Schema::hasTable('bookings')) {
            Schema::drop('bookings');
        }

        if (Schema::hasTable('old_cart_items')) {
            Schema::drop('old_cart_items');
        }

        if (Schema::hasTable('old_carts')) {
            Schema::drop('old_carts');
        }
    }

    public function down(): void
    {
        // Optional: recreate structure if rollback needed

        if (!Schema::hasTable('old_carts')) {
            Schema::create('old_carts', function ($table) {
                $table->id();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('old_cart_items')) {
            Schema::create('old_cart_items', function ($table) {
                $table->id();
                $table->timestamps();
            });
        }
    }
};
