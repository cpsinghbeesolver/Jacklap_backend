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
        Schema::table('professional_details', function (Blueprint $table) {

            if (!Schema::hasColumn('professional_details', 'teaching_mode')) {

                $table->tinyInteger('teaching_mode')
                    ->nullable()
                    ->comment('1=online,2=offline,3=both')
                    ->after('transmission_type');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('professional_details', function (Blueprint $table) {

            if (Schema::hasColumn('professional_details', 'teaching_mode')) {

                $table->dropColumn('teaching_mode');
            }
        });
    }
};