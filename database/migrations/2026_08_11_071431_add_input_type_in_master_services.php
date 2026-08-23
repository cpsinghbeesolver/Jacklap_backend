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
            if (!Schema::hasColumn('master_services', 'input_type')) {
                $table->string('input_type')->after('type')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_services', function (Blueprint $table) {
            if (Schema::hasColumn('master_services', 'input_type')) {
                $table->dropColumn('input_type');
            }
        });
    }
};
