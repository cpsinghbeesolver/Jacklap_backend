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
        Schema::table('master_services', function (Blueprint $table) {

            if (!Schema::hasColumn('master_services', 'subject_type')) {

                $table->tinyInteger('subject_type')
                    ->nullable()
                    ->comment('1=academic,2=non_academic')
                    ->after('service_category_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_services', function (Blueprint $table) {

            if (Schema::hasColumn('master_services', 'subject_type')) {

                $table->dropColumn('subject_type');
            }
        });
    }
};