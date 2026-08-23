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
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {

                if (!Schema::hasColumn('users', 'profile_step')) {
                    $table->tinyInteger('profile_step')
                        ->default(0)
                        ->comment('0=start,1=services,2=idproofs,3=bank details');
                }

            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {

                if (Schema::hasColumn('users', 'profile_step')) {
                    $table->dropColumn('profile_step');
                }

            });
        }
    }
};
