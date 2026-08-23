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
        if (Schema::hasColumn('professional_details', 'experience')) {
            Schema::table('professional_details', function (Blueprint $table) {
                $table->decimal('experience', 10, 2)->change();
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('professional_details', 'experience')) {
            Schema::table('professional_details', function (Blueprint $table) {
                $table->decimal('experience', 3, 2)->change();
            });
        }
    }
};
