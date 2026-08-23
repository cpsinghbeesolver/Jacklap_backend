<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToProfessionalDetailsTable extends Migration
{
    public function up()
    {
        Schema::table('professional_details', function (Blueprint $table) {

            if (!Schema::hasColumn('professional_details', 'transmission_type')) {
                // 0 = automatic, 1 = manual, 2 = both
                $table->tinyInteger('transmission_type')
                      ->default(0)
                      ->comment('0=none,1=automatic,2=manual,3=both')
                      ->after('skills'); // replace with actual column
            }

            if (!Schema::hasColumn('professional_details', 'safety_record')) {
                $table->text('safety_record')
                      ->nullable()
                      ->after('transmission_type');
            }
        });
    }

    public function down()
    {
        Schema::table('professional_details', function (Blueprint $table) {

            if (Schema::hasColumn('professional_details', 'transmission_type')) {
                $table->dropColumn('transmission_type');
            }

            if (Schema::hasColumn('professional_details', 'safety_record')) {
                $table->dropColumn('safety_record');
            }
        });
    }
}
