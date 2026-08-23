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
        // Create table only if not exists
        if (!Schema::hasTable('availability_slots')) {
            Schema::create('availability_slots', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->enum('day', [
                    'monday', 'tuesday', 'wednesday', 'thursday',
                    'friday', 'saturday', 'sunday'
                ]);
                $table->time('opening_time')->nullable();
                $table->time('closing_time')->nullable();
                $table->tinyInteger('status')->default(1); // 1=open, 0=close
                $table->timestamps();

                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('availability_slots');
    }
};
