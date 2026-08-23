<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('enquiries')) {
            return;
        }

        Schema::create('enquiries', function (Blueprint $table) {
            $table->id();

            $table->string('enquiry_number')->nullable()->unique();
            $table->tinyInteger('status')->nullable()->default(0);

            $table->foreignId('user_id')->nullable()
                ->constrained('users')->nullOnDelete();
            $table->foreignId('provider_id')->nullable()
                ->constrained('users')->nullOnDelete();
            $table->foreignId('service_category_id')->nullable()
                ->constrained('service_categories')->nullOnDelete();

            $table->dateTime('start_datetime')->nullable();
            $table->dateTime('end_datetime')->nullable();

            $table->string('duration_type')->nullable();

            $table->float('total_hours')->nullable();
            $table->float('total_amount')->nullable();
            $table->float('discount')->nullable();
            $table->float('tax')->nullable();

            $table->foreignId('address_id')->nullable()
                ->constrained('user_addresses')->nullOnDelete();
            $table->json('address_json')->nullable();

            $table->string('transmission_type')->nullable();
            $table->boolean('is_recurring')->nullable()->default(false);
            $table->integer('recurring_weeks')->nullable();
            $table->json('selected_days')->nullable();
            $table->json('time_slots')->nullable();

            $table->json('service_use_cases')->nullable();
            $table->json('license_types')->nullable();
            $table->json('material_ids')->nullable();
            $table->text('service_requirements')->nullable();

            $table->tinyInteger('service_type')->nullable()->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enquiries');
    }
};