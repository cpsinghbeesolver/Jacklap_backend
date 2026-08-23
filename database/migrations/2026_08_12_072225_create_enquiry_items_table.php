<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('enquiry_items')) {
            return;
        }

        Schema::create('enquiry_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('enquiry_id')->nullable()
                ->constrained('enquiries')->cascadeOnDelete();

            $table->foreignId('service_id')->nullable();
            $table->string('class_name')->nullable();
            $table->foreignId('service_item_id')->nullable();
            $table->string('service_name')->nullable();

            $table->float('quantity')->nullable()->default(1);
            $table->float('price')->nullable();
            $table->float('total_price')->nullable();

            $table->string('type')->nullable();
            $table->tinyInteger('subject_type')->nullable();
            $table->integer('min_people')->nullable();
            $table->integer('max_people')->nullable();

            $table->tinyInteger('service_type')->nullable()->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enquiry_items');
    }
};