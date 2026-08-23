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
        // Only create if table does not exist
        if (!Schema::hasTable('master_services')) {
            Schema::create('master_services', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->unsignedBigInteger('service_category_id');
                $table->text('description')->nullable();
                $table->boolean('is_default')->default(false);
                $table->timestamps();

                $table->unique(['name', 'service_category_id']); // ensure unique service per category
                $table->foreign('service_category_id')->references('id')->on('service_categories')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_services');
    }
};