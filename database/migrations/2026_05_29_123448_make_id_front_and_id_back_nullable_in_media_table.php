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
        if (Schema::hasTable('media')) {

            Schema::table('media', function (Blueprint $table) {

                if (Schema::hasColumn('media', 'id_front')) {

                    $table->string('id_front')->nullable()->change();

                }

                if (Schema::hasColumn('media', 'id_back')) {

                    $table->string('id_back')->nullable()->change();

                }

                if (Schema::hasColumn('media', 'profile_photo')) {

                    $table->string('profile_photo')->nullable()->change();

                }

            });

        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('media')) {

            Schema::table('media', function (Blueprint $table) {

                if (Schema::hasColumn('media', 'id_front')) {

                    $table->string('id_front')->nullable(false)->change();

                }

                if (Schema::hasColumn('media', 'id_back')) {

                    $table->string('id_back')->nullable(false)->change();

                }

                if (Schema::hasColumn('media', 'profile_photo')) {

                    $table->string('profile_photo')->nullable(false)->change();

                }

            });

        }
    }
};