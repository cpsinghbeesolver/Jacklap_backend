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
        if (!Schema::hasColumn('users', 'availability_status')) {
            Schema::table('users', function (Blueprint $table) {
                $table->tinyInteger('availability_status')
                    ->default(0)
                    ->comment('0 = Not Available, 1 = Available, 2 = Busy')
                    ->after('remember_token');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'availability_status')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('availability_status');
            });
        }
    }
};
