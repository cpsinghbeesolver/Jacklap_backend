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

            if (!Schema::hasColumn('services', 'description')) {
                $table->text('description')->nullable()->after('name');
            }

            if (!Schema::hasColumn('services', 'service_category_id')) {
                $table->unsignedBigInteger('service_category_id')
                      ->nullable()
                      ->after('description');

                // Add FK only if column exists (safe)
                $table->foreign('service_category_id')
                      ->references('id')
                      ->on('service_categories')
                      ->onDelete('set null');
            }

            if (!Schema::hasColumn('services', 'is_default')) {
                $table->boolean('is_default')
                      ->default(false)
                      ->after('service_category_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {

            if (Schema::hasColumn('services', 'service_category_id')) {
                // Drop FK first if exists
                try {
                    $table->dropForeign(['service_category_id']);
                } catch (\Exception $e) {}
            }

            $columns = [];

            if (Schema::hasColumn('services', 'description')) {
                $columns[] = 'description';
            }

            if (Schema::hasColumn('services', 'service_category_id')) {
                $columns[] = 'service_category_id';
            }

            if (Schema::hasColumn('services', 'is_default')) {
                $columns[] = 'is_default';
            }

            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
