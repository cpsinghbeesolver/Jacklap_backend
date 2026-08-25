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
        if (Schema::hasTable('booking_images')) {
            return;
        }
        Schema::create('booking_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')
            ->constrained('bookings')
            ->cascadeOnDelete();

            $table->string('path', 500);
            $table->enum('type', ['before', 'after']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_images');
    }
};
