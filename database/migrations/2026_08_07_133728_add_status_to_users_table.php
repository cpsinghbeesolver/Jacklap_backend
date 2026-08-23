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
            if (!Schema::hasColumn('users', 'is_active') && !Schema::hasColumn('users', 'deactivated_at') && !Schema::hasColumn('users', 'deactivated_by') && !Schema::hasColumn('users', 'deactivation_reason')) {
                $table->boolean('is_active')->default(true);
                $table->timestamp('deactivated_at')->nullable();
                $table->foreignId('deactivated_by')->nullable()->constrained('users');
                $table->text('deactivation_reason')->nullable();
            }  
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'is_active')) {
                $table->dropColumn('is_active');
            }
            if (Schema::hasColumn('users', 'deactivated_at')) {
                $table->dropColumn('deactivated_at');
            }
            if (Schema::hasColumn('users', 'deactivated_by')) {
                $table->dropForeign(['deactivated_by']);
                $table->dropColumn('deactivated_by');
            }
            if (Schema::hasColumn('users', 'deactivation_reason')) {
                $table->dropColumn('deactivation_reason');
            }
        });
    }
};
