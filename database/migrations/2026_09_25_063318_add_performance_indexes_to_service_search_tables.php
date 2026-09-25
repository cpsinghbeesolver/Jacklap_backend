<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function indexExists(string $table, string $indexName): bool
    {
        $result = DB::select("SHOW INDEX FROM `$table` WHERE Key_name = ?", [$indexName]);
        return count($result) > 0;
    }

    private function addIndexIfMissing(string $table, array $columns, string $indexName): void
    {
        if ($this->indexExists($table, $indexName)) {
            return; // already there from a previous run — skip safely
        }

        Schema::table($table, function (Blueprint $blueprint) use ($columns, $indexName) {
            $blueprint->index($columns, $indexName);
        });
    }

    private function dropIndexIfExists(string $table, string $indexName): void
    {
        if (!$this->indexExists($table, $indexName)) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($indexName) {
            $blueprint->dropIndex($indexName);
        });
    }

    public function up(): void
    {
        $this->addIndexIfMissing('professional_details', ['service_category_id', 'teaching_mode', 'transmission_type'], 'idx_cat_mode_trans');
        $this->addIndexIfMissing('services', ['user_id', 'service_id', 'subject_type', 'class_name'], 'idx_user_service');
        $this->addIndexIfMissing('services', ['service_id', 'user_id'], 'idx_service_user');
        $this->addIndexIfMissing('user_license_types', ['user_id', 'license_type_id'], 'idx_user_license');
        $this->addIndexIfMissing('provider_materials', ['user_id', 'material_type_id'], 'idx_user_material');
        $this->addIndexIfMissing('user_service_usecases', ['user_id', 'service_usecase_id'], 'idx_user_usecase');
        $this->addIndexIfMissing('availability_slots', ['user_id', 'day', 'status', 'opening_time', 'closing_time'], 'idx_slot_lookup');
        $this->addIndexIfMissing('bookings', ['user_id', 'status', 'start_datetime', 'end_datetime'], 'idx_booking_lookup');
        $this->addIndexIfMissing('users', ['latitude', 'longitude'], 'idx_lat_lng');
    }

    public function down(): void
    {
        $this->dropIndexIfExists('professional_details', 'idx_cat_mode_trans');
        $this->dropIndexIfExists('services', 'idx_user_service');
        $this->dropIndexIfExists('services', 'idx_service_user');
        $this->dropIndexIfExists('license_types', 'idx_user_license');
        $this->dropIndexIfExists('provider_materials', 'idx_user_material');
        $this->dropIndexIfExists('service_usecases', 'idx_user_usecase');
        $this->dropIndexIfExists('availability_slots', 'idx_slot_lookup');
        $this->dropIndexIfExists('provider_bookings', 'idx_booking_lookup');
        $this->dropIndexIfExists('users', 'idx_lat_lng');
    }
};