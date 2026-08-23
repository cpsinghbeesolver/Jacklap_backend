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
        if (!Schema::hasTable('service_use_cases')) {

            Schema::create('service_use_cases', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('service_category_id')->nullable();
                $table->string('title');
                $table->timestamps();

                // Foreign key (only if table exists)
                if (Schema::hasTable('service_categories')) {
                    $table->foreign('service_category_id')
                        ->references('id')
                        ->on('service_categories')
                        ->cascadeOnDelete();
                }
            });

        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_use_cases');
    }
};
