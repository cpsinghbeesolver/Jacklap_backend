<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        if (!Schema::hasTable('files')) {
            Schema::create('files', function (Blueprint $table) {
                $table->id();
        
                $table->string('original_name');  
                $table->string('path');           
                $table->string('mime_type')->nullable();
                $table->unsignedBigInteger('size')->nullable();
        
                // morph relation
                $table->nullableMorphs('fileable');
        
                // optional improvements
                $table->boolean('is_primary')->default(false);
                $table->integer('sort_order')->default(0);
        
                $table->timestamps();
        
                // index for performance
                $table->index(['fileable_id', 'fileable_type']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};
