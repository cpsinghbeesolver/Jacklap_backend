<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // master_services — extend enum to include new types
        Schema::table('master_services', function (Blueprint $table) {
            // Add 'package', 'specialization', 'product' to existing enum
            $table->enum('type', [
                'service',
                'subject',
                'skill',
                'property',
                'package',        // ← new
                'specialization', // ← new
                'product',        // ← new
            ])->default('service')->change();

            // subject_type: extend to support Male/Female for salon
            // 1=academic, 2=non_academic, 3=Male, 4=Female, 5=Both
            $table->tinyInteger('subject_type')->nullable()
                ->comment('1=academic, 2=non_academic, 3=Male, 4=Female, 5=Both')
                ->change();

            if (!Schema::hasColumn('master_services', 'sort_order')) {
                $table->integer('sort_order')->default(0)->after('price_limit');
            }
        });

        // master_service_items — add is_optional
        Schema::table('master_service_items', function (Blueprint $table) {
            if (!Schema::hasColumn('master_service_items', 'is_optional')) {
                $table->boolean('is_optional')->default(false)->after('description');
            }
        });

        // services — add missing columns
        Schema::table('services', function (Blueprint $table) {
            if (!Schema::hasColumn('services', 'type')) {
                $table->string('type')->default('service')->after('service_item_id');
            }
            if (!Schema::hasColumn('services', 'pricing_type')) {
                $table->string('pricing_type')->nullable()->after('price');
            }
            if (!Schema::hasColumn('services', 'min_people')) {
                $table->integer('min_people')->nullable()->after('pricing_type');
            }
            if (!Schema::hasColumn('services', 'max_people')) {
                $table->integer('max_people')->nullable()->after('min_people');
            }
            if (!Schema::hasColumn('services', 'custom_value')) {
                $table->string('custom_value')->nullable()->after('max_people');
            }
        });
    }

    public function down(): void
    {
        // Revert enum back to original
        Schema::table('master_services', function (Blueprint $table) {
            $table->enum('type', [
                'service',
                'subject',
                'skill',
                'property',
            ])->default('service')->change();

            $table->tinyInteger('subject_type')->nullable()
                ->comment('1=academic, 2=non_academic')
                ->change();

            if (Schema::hasColumn('master_services', 'sort_order')) {
                $table->dropColumn('sort_order');
            }
        });

        Schema::table('master_service_items', function (Blueprint $table) {
            $table->dropColumn('is_optional');
        });

        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn(['type', 'pricing_type', 'min_people', 'max_people', 'custom_value']);
        });
    }
};