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
        if (!Schema::hasColumn('services', 'service_id')) {
            Schema::table('services', function (Blueprint $table) {
                $table->unsignedBigInteger('service_id')->nullable()->after('id');

                // Optional: add foreign key if you want to enforce master service relation
                $table->foreign('service_id')
                      ->references('id')
                      ->on('master_services')
                      ->onDelete('set null');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('services', 'service_id')) {
            Schema::table('services', function (Blueprint $table) {
                $table->dropForeign(['service_id']);
                $table->dropColumn('service_id');
            });
        }
    }
};
