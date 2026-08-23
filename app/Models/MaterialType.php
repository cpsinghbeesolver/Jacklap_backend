<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaterialType extends Model
{
    protected $fillable = [
        'service_category_id',
        'name',
        'status'
    ];

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function serviceCategory()
    {
        return $this->belongsTo(ServiceCategory::class);
    }

    public function providerMaterials()
    {
        return $this->hasMany(ProviderMaterial::class);
    }
}