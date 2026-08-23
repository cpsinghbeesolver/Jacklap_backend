<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('addon_services')) {

            Schema::create('addon_services', function (Blueprint $table) {
                $table->id();

                $table->unsignedBigInteger('user_id');
                $table->string('name');

                $table->string('type')->nullable()->comment('service | subject | skill | tool');

                $table->tinyInteger('status')->default(1)->comment('0 = inactive, 1 = active');

                $table->unsignedBigInteger('service_category_id')->nullable();

                $table->text('description')->nullable();

                $table->boolean('is_default')->default(false);

                $table->decimal('price', 10, 2)->nullable();

                $table->timestamps();

                // Foreign keys
                $table->foreign('user_id')
                      ->references('id')
                      ->on('users')
                      ->onDelete('cascade');

                $table->foreign('service_category_id')
                      ->references('id')
                      ->on('service_categories')
                      ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('addon_services')) {
            Schema::dropIfExists('addon_services');
        }
    }
};
