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
        if (Schema::hasTable('identity_types')) {

            Schema::table('identity_types', function (Blueprint $table) {

                if (!Schema::hasColumn('identity_types', 'total_documents')) {

                    $table->integer('total_documents')
                          ->default(1)
                          ->after('name');

                }

            });

        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (
            Schema::hasTable('identity_types') &&
            Schema::hasColumn('identity_types', 'total_documents')
        ) {

            Schema::table('identity_types', function (Blueprint $table) {

                $table->dropColumn('total_documents');

            });

        }
    }
};