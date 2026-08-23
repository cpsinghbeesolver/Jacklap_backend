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
        if (!Schema::hasTable('user_languages')) {
            Schema::create('user_languages', function (Blueprint $table) {
                $table->id();

                $table->foreignId('user_id')
                    ->constrained()
                    ->cascadeOnDelete();

                $table->string('language');
                $table->string('proficiency')->nullable();

                $table->timestamps();
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('user_languages')) {
            Schema::dropIfExists('user_languages');
        }
    }
};
