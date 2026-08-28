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
            if (!Schema::hasColumn('users', 'stripe_account_id') && !Schema::hasColumn('users', 'stripe_onboarding_complete')) {
                $table->string('stripe_account_id')->nullable()->unique();
                $table->boolean('stripe_onboarding_complete')->default(false);
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
        });
    }
};
