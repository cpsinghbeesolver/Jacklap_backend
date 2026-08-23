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
        if (!Schema::hasTable('user_license_types')) {

            Schema::create('user_license_types', function (Blueprint $table) {

                $table->id();

                $table->unsignedBigInteger('user_id');

                $table->unsignedBigInteger('license_type_id');

                $table->timestamps();

                $table->foreign('user_id')
                    ->references('id')
                    ->on('users')
                    ->onDelete('cascade');

                $table->foreign('license_type_id')
                    ->references('id')
                    ->on('license_types')
                    ->onDelete('cascade');

                $table->unique([
                    'user_id',
                    'license_type_id'
                ]);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_license_types');
    }
};