<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        if (Schema::hasTable('master_services') && !Schema::hasColumn('master_services', 'price_limit')) {
            Schema::table('master_services', function (Blueprint $table) {
                $table->decimal('price_limit', 10, 2)->nullable()->after('is_default');
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('master_services') && Schema::hasColumn('master_services', 'price_limit')) {
            Schema::table('master_services', function (Blueprint $table) {
                $table->dropColumn('price_limit');
            });
        }
    }
};
