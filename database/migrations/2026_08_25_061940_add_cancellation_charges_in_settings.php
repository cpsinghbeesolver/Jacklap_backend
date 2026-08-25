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
        Schema::table('settings', function (Blueprint $table) {
            if (!Schema::hasColumn('settings', 'platform_fee_type')) {
                $table->enum('platform_fee_type', ['perc', 'num'])->nullable()->after('platform_fee');
            }

            if (!Schema::hasColumn('settings', 'cancellation_charges')) {
                $table->decimal('cancellation_charges', 10, 2)->nullable();
            }
            if (!Schema::hasColumn('settings', 'cancellation_charges_type')) {
                $table->enum('cancellation_charges_type', ['perc', 'num'])->nullable()->after('cancellation_charges');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (Schema::hasColumn('settings', 'cancellation_charges')) {
                $table->dropColumn('cancellation_charges');
            }
            if (Schema::hasColumn('settings', 'cancellation_charges_type')) {
                $table->dropColumn('cancellation_charges_type');
            }
            if (Schema::hasColumn('settings', 'platform_fee_type')) {
                $table->dropColumn('platform_fee_type');
            }
        });
    }
};
