<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterServiceItem extends Model
{
    protected $fillable = [
        'master_service_id',
        'name',
        'description',
        'status',
        'sort_order',
        'is_optional',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function masterService()
    {
        return $this->belongsTo(MasterService::class);
    }
}