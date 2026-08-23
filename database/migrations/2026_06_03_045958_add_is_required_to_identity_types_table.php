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
        Schema::table('identity_types', function (Blueprint $table) {
            if (!Schema::hasColumn('identity_types', 'is_required')) {
                $table->boolean('is_required')
                    ->default(true)
                    ->after('total_documents');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('identity_types', function (Blueprint $table) {
            if (Schema::hasColumn('identity_types', 'is_required')) {
                $table->dropColumn('is_required');
            }
        });
    }
};
