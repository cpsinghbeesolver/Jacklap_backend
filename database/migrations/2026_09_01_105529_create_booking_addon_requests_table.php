<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('booking_addon_requests')) {
            Schema::create('booking_addon_requests', function (Blueprint $table) {
                $table->id();

                $table->foreignId('booking_id')
                    ->constrained('bookings')
                    ->cascadeOnDelete();

                $table->foreignId('requested_by')
                    ->constrained('users');

                $table->json('items');

                // Example:
                // [
                //     {
                //         "service_id": 1,
                //         "service_name": "Extra Consultation",
                //         "quantity": 2,
                //         "price": 500
                //     }
                // ]

                $table->decimal('total_amount', 10, 2)
                    ->default(0);

                $table->enum('status', [
                    'pending',
                    'accepted',
                    'rejected',
                ])->default('pending');

                $table->string('reject_reason')
                    ->nullable();

                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('booking_addon_requests')) {
            Schema::dropIfExists('booking_addon_requests');
        }
    }
};