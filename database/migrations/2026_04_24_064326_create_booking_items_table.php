<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('booking_items')) {

            Schema::create('booking_items', function (Blueprint $table) {
                $table->id();

                $table->unsignedBigInteger('booking_id');
                $table->unsignedBigInteger('service_id')->nullable();

                $table->string('service_name');

                $table->decimal('quantity', 8, 2)->default(1);
                $table->decimal('price', 10, 2)->default(0);
                $table->decimal('total_price', 10, 2)->default(0);

                $table->timestamps();

                $table->index('booking_id');

                $table->foreign('booking_id')
                      ->references('id')
                      ->on('bookings')
                      ->onDelete('cascade');
            });

        }
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_items');
    }
};
