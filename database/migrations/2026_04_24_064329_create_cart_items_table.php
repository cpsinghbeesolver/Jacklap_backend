<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('cart_items')) {

            Schema::create('cart_items', function (Blueprint $table) {
                $table->id();

                $table->unsignedBigInteger('cart_id');
                $table->unsignedBigInteger('service_id');
                $table->string('service_name');

                $table->decimal('quantity', 8, 2)->default(1);

                $table->decimal('price', 10, 2)->default(0);
                $table->decimal('total_price', 10, 2)->default(0);

                $table->timestamps();

                $table->index('cart_id');
                $table->index('service_id');

                $table->foreign('cart_id')
                      ->references('id')
                      ->on('carts')
                      ->onDelete('cascade');
            });

        }
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};
