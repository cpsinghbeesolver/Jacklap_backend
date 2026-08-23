<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('booking_concerns')) {
            Schema::create('booking_concerns', function (Blueprint $table) {
                $table->id();

                $table->foreignId('booking_id')
                    ->constrained('bookings')
                    ->cascadeOnDelete();

                $table->foreignId('user_id')
                    ->constrained('users')
                    ->cascadeOnDelete();

                // seeker / provider
                $table->enum('type', ['seeker', 'provider']);

                // User against whom the concern is raised
                $table->foreignId('against_user_id')
                    ->constrained('users')
                    ->cascadeOnDelete();

                $table->string('reason');
                $table->text('description')->nullable();

                $table->string('status')
                    ->default('pending');

                $table->text('admin_response')->nullable();
                $table->timestamp('resolved_at')->nullable();

                $table->timestamps();
                $table->index(['booking_id', 'user_id']);
                $table->index(['against_user_id']);
                $table->index(['type', 'status']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_concerns');
    }
};