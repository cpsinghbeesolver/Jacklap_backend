<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('payments')) {

            Schema::create('payments', function (Blueprint $table) {
                $table->id();

                $table->foreignId('booking_id')
                    ->constrained()
                    ->cascadeOnDelete();

                $table->foreignId('user_id')
                    ->constrained()
                    ->cascadeOnDelete();

                $table->string('stripe_payment_intent_id')
                    ->unique();

                $table->string('payment_method')
                    ->default('card');

                $table->decimal('amount', 10, 2);

                $table->string('currency', 10)
                    ->default('usd');

                $table->string('status')
                    ->default('pending');

                $table->json('stripe_response')
                    ->nullable();

                $table->timestamps();
            });

        }
    }

    public function down(): void
    {
        if (Schema::hasTable('payments')) {
            Schema::dropIfExists('payments');
        }
    }
};