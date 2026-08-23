<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            if (!Schema::hasColumn('cart_items', 'type')) {
                $table->enum('type', ['service', 'package', 'specialization', 'product'])->nullable()->after('service_item_id');
            }
            if (!Schema::hasColumn('cart_items', 'subject_type')) {
                $table->tinyInteger('subject_type')->nullable()->comment('1=academic,2=non academic,3=Male, 4=Female, 5=Both')->after('type');
            }
            if (!Schema::hasColumn('cart_items', 'min_people')) {
                $table->integer('min_people')->nullable()->after('subject_type');
            }
            if (!Schema::hasColumn('cart_items', 'max_people')) {
                $table->integer('max_people')->nullable()->after('min_people');
            }
        });

        Schema::table('booking_items', function (Blueprint $table) {
            if (!Schema::hasColumn('booking_items', 'type')) {
                $table->enum('type', ['service', 'package', 'specialization', 'product'])->nullable()->after('service_item_id');
            }
            if (!Schema::hasColumn('booking_items', 'subject_type')) {
                $table->tinyInteger('subject_type')->nullable()->comment('1=academic,2=non academic,3=Male, 4=Female, 5=Both')->after('type');
            }
            if (!Schema::hasColumn('booking_items', 'min_people')) {
                $table->integer('min_people')->nullable()->after('subject_type');
            }
            if (!Schema::hasColumn('booking_items', 'max_people')) {
                $table->integer('max_people')->nullable()->after('min_people');
            }
        });
    }

    public function down(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropColumn(['type', 'subject_type', 'min_people', 'max_people']);
        });

        Schema::table('booking_items', function (Blueprint $table) {
            $table->dropColumn(['type', 'subject_type', 'min_people', 'max_people']);
        });
    }
};