<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProviderMaterial extends Model
{
    protected $fillable = [
        'user_id',
        'service_category_id',
        'material_type_id',
        'price'
    ];

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function serviceCategory()
    {
        return $this->belongsTo(ServiceCategory::class);
    }

    public function materialType()
    {
        return $this->belongsTo(MaterialType::class);
    }
}