<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        
    if (!Schema::hasTable('contact_us')) {
        Schema::create('contact_us', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('subject');
            $table->tinyInteger('profile')
                        ->default(0)
                        ->comment('0=provider,1=seeker');
            $table->text('message');
            $table->timestamps();
         });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_us');
    }
};

