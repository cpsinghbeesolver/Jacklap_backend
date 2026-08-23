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
        if (!Schema::hasTable('bank_details')) {

            Schema::create('bank_details', function (Blueprint $table) {
                $table->id();

                $table->foreignId('user_id')->constrained()->onDelete('cascade');

                $table->string('account_holder_name');
                $table->string('bank_name');
                $table->string('account_number');
                $table->string('ifsc_code');
                $table->string('account_type')->nullable();

                $table->timestamps();
            });

        }
    }

    public function down()
    {
        if (Schema::hasTable('bank_details')) {
            Schema::dropIfExists('bank_details');
        }
    }
};
