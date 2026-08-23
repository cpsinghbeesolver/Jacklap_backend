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
        if (!Schema::hasTable('user_service_usecases')) {

            Schema::create('user_service_usecases', function (Blueprint $table) {

                $table->id();

                $table->unsignedBigInteger('user_id');

                $table->unsignedBigInteger('service_usecase_id');

                $table->timestamps();

                $table->foreign('user_id')
                    ->references('id')
                    ->on('users')
                    ->onDelete('cascade');

                $table->foreign('service_usecase_id')
                    ->references('id')
                    ->on('service_use_cases')
                    ->onDelete('cascade');

                $table->unique([
                    'user_id',
                    'service_usecase_id'
                ]);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_service_usecases');
    }
};
