<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{

    protected $fillable = [
        'name',
        'class_name',
        'subject_type',
        'description',
        'is_default',
        'service_category_id',
        'price',
        'user_id',
        'service_id',
        'service_item_id',
        'type', 
        'pricing_type',
        'min_people',
        'max_people',
        'custom_value'
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function service(){
        return $this->belongsTo(MasterService::class);
    }

}
