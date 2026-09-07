<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('payout_requests')) {
            Schema::create('payout_requests', function (Blueprint $table) {
                $table->id();

                $table->foreignId('provider_id')
                    ->constrained('users')
                    ->cascadeOnDelete();

                $table->string('stripe_account_id');

                $table->decimal('amount', 10, 2);

                $table->string('currency', 3);

                // Provider's available/earned balance at request time
                $table->decimal('available_balance_snapshot', 10, 2)
                    ->nullable();

                $table->enum('status', [
                    'pending',
                    'rejected',
                    'transferred',
                    'processing',
                    'paid',
                    'failed',
                ])->default('pending');

                $table->string('transfer_id')
                    ->nullable();

                $table->string('stripe_payout_id')
                    ->nullable()
                    ->index();

                $table->text('admin_note')
                    ->nullable();

                $table->text('rejection_reason')
                    ->nullable();

                $table->foreignId('processed_by')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->timestamp('processed_at')
                    ->nullable();

                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('payout_requests');
    }
};