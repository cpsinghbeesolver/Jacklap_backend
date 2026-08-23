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
        Schema::table('services', function (Blueprint $table) {

            if (!Schema::hasColumn('services', 'subject_type')) {

                $table->tinyInteger('subject_type')
                    ->nullable()
                    ->comment('1=academic,2=non_academic')
                    ->after('service_category_id');
            }

            if (!Schema::hasColumn('services', 'class_name')) {

                $table->string('class_name')
                    ->nullable()
                    ->after('subject_type');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {

            if (Schema::hasColumn('services', 'subject_type')) {

                $table->dropColumn('subject_type');
            }

            if (Schema::hasColumn('services', 'class_name')) {

                $table->dropColumn('class_name');
            }
        });
    }
};