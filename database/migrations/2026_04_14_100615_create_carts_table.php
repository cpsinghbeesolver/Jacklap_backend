<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        if (!Schema::hasTable('carts')) {
            Schema::create('carts', function (Blueprint $table) {
                $table->id();

                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('provider_id')->nullable()->constrained('users')->nullOnDelete();

                $table->decimal('main_total', 10, 2)->default(0);
                $table->decimal('addon_total', 10, 2)->default(0);
                $table->decimal('tax', 10, 2)->default(0);
                $table->decimal('discount', 10, 2)->default(0);
                $table->decimal('payable_amount', 10, 2)->default(0);

                $table->foreignId('address_id')->nullable();

                $table->date('scheduled_date')->nullable();
                $table->time('scheduled_time')->nullable();

                $table->enum('status', ['cart', 'checked_out', 'cancelled'])->default('cart');

                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};
