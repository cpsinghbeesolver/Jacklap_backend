<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('master_service_items')) {

            Schema::create('master_service_items', function (Blueprint $table) {

                $table->id();

                /*
                |--------------------------------------------------------------------------
                | Parent Service
                |--------------------------------------------------------------------------
                | Example:
                | General Cleaning
                | Deep Cleaning
                |--------------------------------------------------------------------------
                */

                $table->foreignId('master_service_id')
                    ->constrained('master_services')
                    ->onDelete('cascade');

                /*
                |--------------------------------------------------------------------------
                | Sub Service Name
                |--------------------------------------------------------------------------
                | Example:
                | Dusting
                | Kitchen surface cleaning
                | Bathroom cleaning
                |--------------------------------------------------------------------------
                */

                $table->string('name');

                /*
                |--------------------------------------------------------------------------
                | Optional Description
                |--------------------------------------------------------------------------
                */

                $table->text('description')->nullable();

                /*
                |--------------------------------------------------------------------------
                | Status
                |--------------------------------------------------------------------------
                */

                $table->boolean('status')->default(1);

                /*
                |--------------------------------------------------------------------------
                | Sorting Order
                |--------------------------------------------------------------------------
                */

                $table->integer('sort_order')->default(0);

                $table->timestamps();

                /*
                |--------------------------------------------------------------------------
                | Prevent Duplicate Items
                |--------------------------------------------------------------------------
                */

                $table->unique([
                    'master_service_id',
                    'name'
                ]);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('master_service_items');
    }
};