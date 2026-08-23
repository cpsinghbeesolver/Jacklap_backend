<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('bookings')) {

            Schema::create('bookings', function (Blueprint $table) {
                $table->id();
                $table->string('booking_number')->nullable();

                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('provider_id')->nullable();
                $table->unsignedBigInteger('service_category_id')->nullable();

                $table->dateTime('start_datetime')->nullable();
                $table->dateTime('end_datetime')->nullable();

                $table->decimal('total_hours', 8, 2)->default(0);
                $table->decimal('total_amount', 10, 2)->default(0);

                $table->decimal('discount', 10, 2)->default(0);
                $table->decimal('tax', 10, 2)->default(0);
                $table->decimal('payable_amount', 10, 2)->default(0);

                $table->unsignedBigInteger('address_id')->nullable();
                $table->json('address_json')->nullable();

                $table->enum('status', [
                    'pending',
                    'confirmed',
                    'in_progress',
                    'completed',
                    'cancelled'
                ])->default('pending');

                $table->timestamps();

                $table->index('user_id');
                $table->index('provider_id');
            });

        }
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
