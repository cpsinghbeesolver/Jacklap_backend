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
        if(!Schema::hasTable('media')){
            Schema::create('media', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('identity_type_id');
                $table->string('id_front');
                $table->string('id_back');
                $table->string('certificate')->nullable();
                $table->string('profile_photo');
                $table->unsignedBigInteger('user_id');
                $table->timestamps();

                $table->foreign('identity_type_id')->references('id')->on('identity_types')->onDelete('restrict');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
