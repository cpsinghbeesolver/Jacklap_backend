<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('provider_materials')) {

            Schema::create('provider_materials', function (Blueprint $table) {

                $table->id();

                $table->foreignId('user_id')
                    ->constrained()
                    ->onDelete('cascade');

                $table->foreignId('service_category_id')
                    ->constrained('service_categories')
                    ->onDelete('cascade');

                $table->foreignId('material_type_id')
                    ->constrained('material_types')
                    ->onDelete('cascade');

                $table->decimal('price', 10, 2)
                    ->default(0);

                $table->timestamps();

                $table->unique([
                    'user_id',
                    'material_type_id'
                ]);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_materials');
    }
};