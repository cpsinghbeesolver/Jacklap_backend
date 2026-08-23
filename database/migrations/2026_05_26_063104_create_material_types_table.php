<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('material_types')) {

            Schema::create('material_types', function (Blueprint $table) {

                $table->id();

                $table->foreignId('service_category_id')
                    ->constrained('service_categories')
                    ->onDelete('cascade');

                $table->string('name');

                $table->boolean('status')->default(1);

                $table->timestamps();

                $table->unique([
                    'service_category_id',
                    'name'
                ]);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('material_types');
    }
};