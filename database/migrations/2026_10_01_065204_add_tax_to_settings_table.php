<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (
            Schema::hasTable('settings') &&
            !Schema::hasColumn('settings', 'tax')
        ) {
            Schema::table('settings', function (Blueprint $table) {
                $table->decimal('tax', 5, 2)
                    ->default(0)
                    ->after('platform_fee');
            });
        }
    }

    public function down(): void
    {
        if (
            Schema::hasTable('settings') &&
            Schema::hasColumn('settings', 'tax')
        ) {
            Schema::table('settings', function (Blueprint $table) {
                $table->dropColumn('tax');
            });
        }
    }
};