<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EnquiryItem extends Model
{
    protected $fillable = [
        'enquiry_id',
        'service_id',
        'class_name',
        'service_item_id',
        'service_name',
        'quantity',
        'price',
        'total_price',
        'type',
        'subject_type',
        'min_people',
        'max_people',
        'service_type',
    ];

    protected $casts = [
        'quantity' => 'float',
        'price' => 'float',
        'total_price' => 'float',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function enquiry()
    {
        return $this->belongsTo(Enquiry::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    public function addonService()
    {
        return $this->belongsTo(AddonService::class, 'service_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Boot (auto total calculation)
    |--------------------------------------------------------------------------
    */

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($item) {
            if ($item->quantity !== null && $item->price !== null) {
                $item->total_price = $item->quantity * $item->price;
            }
        });
    }
}