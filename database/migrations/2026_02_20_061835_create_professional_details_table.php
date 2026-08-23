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
        if(!Schema::hasTable('professional_details')){
            Schema::create('professional_details', function (Blueprint $table) {
                $table->id();
                $table->decimal('price', 10, 2);
                $table->decimal('experience', 3, 2);
                $table->string('languages');
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('service_category_id');
                $table->timestamps();
        
                // Foreign Key
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('service_category_id')->references('id')->on('service_categories')->onDelete('restrict');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {   
        if(Schema::hasTable('professional_details')){
            Schema::dropIfExists('professional_details');
        }
    }
};
