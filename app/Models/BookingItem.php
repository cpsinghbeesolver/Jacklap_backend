<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingItem extends Model
{
    protected $fillable = [
        'booking_id',
        'service_id',
        'service_name',
        'class_name',
        'service_item_id',
        'quantity',
        'price',
        'total_price',
        'type',
        'subject_type',
        'min_people',
        'max_people',
        'is_reviewed',
        'service_type'
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

    public function booking()
    {
        return $this->belongsTo(Booking::class);
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
    | Auto total calculation
    |--------------------------------------------------------------------------
    */

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($item) {
            if (empty($item->total_price)) {
                $item->total_price = $item->quantity * $item->price;
            }
        });
    }
}