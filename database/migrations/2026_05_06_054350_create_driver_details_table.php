<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDriverDetailsTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('driver_details')) {
            Schema::create('driver_details', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('service_usecase_id');
                $table->unsignedBigInteger('license_type_id');
                $table->timestamps();

                // Optional foreign keys
                // $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                // $table->foreign('service_usecase_id')->references('id')->on('service_usecases')->onDelete('cascade');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('driver_details');
    }
}
