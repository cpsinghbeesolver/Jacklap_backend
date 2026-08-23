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
        if (Schema::hasTable('service_categories')) {

            Schema::table('service_categories', function (Blueprint $table) {

                if (!Schema::hasColumn('service_categories', 'slug')) {
                    $table->string('slug')->unique()->after('name');
                }

                if (!Schema::hasColumn('service_categories', 'image')) {
                    $table->string('image')->nullable()->after('price');
                }

                if (!Schema::hasColumn('service_categories', 'status')) {
                    $table->boolean('status')->default(1)->after('image');
                }

                if (!Schema::hasColumn('service_categories', 'sort_order')) {
                    $table->integer('sort_order')->default(0)->after('status');
                }
            });
        }
    }

    public function down(): void
    {
        Schema::table('service_categories', function (Blueprint $table) {

            if (Schema::hasColumn('service_categories', 'slug')) {
                $table->dropColumn('slug');
            }

            if (Schema::hasColumn('service_categories', 'image')) {
                $table->dropColumn('image');
            }

            if (Schema::hasColumn('service_categories', 'status')) {
                $table->dropColumn('status');
            }

            if (Schema::hasColumn('service_categories', 'sort_order')) {
                $table->dropColumn('sort_order');
            }
        });
    }
};
