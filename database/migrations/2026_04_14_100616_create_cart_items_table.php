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
        if (!Schema::hasTable('cart_items')) {
            Schema::create('cart_items', function (Blueprint $table) {
                $table->id();

                $table->foreignId('cart_id')->constrained('carts')->cascadeOnDelete();
                $table->foreignId('service_id')->constrained('master_services')->cascadeOnDelete();

                $table->string('service_name');
                $table->string('grade')->nullable();

                $table->integer('duration'); // minutes or hours

                $table->decimal('price', 10, 2);
                $table->integer('quantity')->default(1);
                $table->decimal('total', 10, 2);

                // 🔥 important
                $table->enum('type', ['main', 'addon'])->default('main');

                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};
