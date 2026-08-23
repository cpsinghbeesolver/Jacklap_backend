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
        if (!Schema::hasTable('user_addresses')) {

            Schema::create('user_addresses', function (Blueprint $table) {
                $table->id();

                $table->unsignedBigInteger('user_id')->nullable()->index();

                $table->integer('address_type')->nullable();
                $table->string('type_name')->nullable();

                $table->text('address')->nullable();
                $table->string('city')->nullable();
                $table->string('state')->nullable();
                $table->string('country')->nullable();
                $table->string('pincode', 20)->nullable();
                $table->string('latitude', 20)->nullable();
                $table->string('longitude', 20)->nullable();
                $table->string('country_code', 10)->nullable();
                $table->string('phone_number', 20)->nullable();

                $table->string('email')->nullable();
                $table->string('name')->nullable();

                $table->boolean('is_default')->default(false);

                $table->timestamps();

                $table->foreign('user_id')
                      ->references('id')
                      ->on('users')
                      ->onDelete('cascade');
            });

        }
    }

    public function down(): void
    {
        if (Schema::hasTable('user_addresses')) {
            Schema::dropIfExists('user_addresses');
        }
    }
};
