<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('provider_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('seeker_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->timestamp('last_message_at')->nullable();

            $table->timestamps();

            $table->index(['provider_id', 'seeker_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};