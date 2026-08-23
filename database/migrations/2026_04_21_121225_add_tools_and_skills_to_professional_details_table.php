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

            if (!Schema::hasColumn('professional_details', 'tools')) {
                $table->text('tools')->nullable()->after('languages');
            }

            if (!Schema::hasColumn('professional_details', 'skills')) {
                $table->text('skills')->nullable()->after('tools');
            }

        });
    }

    public function down(): void
    {
        Schema::table('professional_details', function (Blueprint $table) {

            if (Schema::hasColumn('professional_details', 'tools')) {
                $table->dropColumn('tools');
            }

            if (Schema::hasColumn('professional_details', 'skills')) {
                $table->dropColumn('skills');
            }

        });
    }
};
