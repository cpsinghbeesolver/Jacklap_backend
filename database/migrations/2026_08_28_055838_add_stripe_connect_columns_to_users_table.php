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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'stripe_account_id')) {
                $table->string('stripe_account_id')->nullable()->unique();
            }
            if (!Schema::hasColumn('users', 'stripe_onboarding_complete')) {
                $table->boolean('stripe_onboarding_complete')->default(false);
            }
            if (!Schema::hasColumn('users', 'stripe_charges_enabled')) {
                $table->boolean('stripe_charges_enabled')->default(false);
            }
            if (!Schema::hasColumn('users', 'stripe_payouts_enabled')) {
                $table->boolean('stripe_payouts_enabled')->default(false);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'stripe_account_id')) {
                $table->dropColumn('stripe_account_id');
            }
            if (Schema::hasColumn('users', 'stripe_onboarding_complete')) {
                $table->dropColumn('stripe_onboarding_complete');
            }
            if (Schema::hasColumn('users', 'stripe_charges_enabled')) {
                $table->dropColumn('stripe_charges_enabled');
            }
            if (Schema::hasColumn('users', 'stripe_payouts_enabled')) {
                $table->dropColumn('stripe_payouts_enabled');
            }
        });
    }
};
