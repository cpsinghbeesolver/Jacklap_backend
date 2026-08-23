<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $fillable = [
        'user_id',
        'provider_id',
        'service_category_id',
    
        'start_datetime',
        'end_datetime',
    
        'duration_type',
    
        'total_hours',
        'total_amount',
        'discount',
        'tax',
    
        'address_id',
        'address_json',
        'transmission_type',
        'is_recurring',
        'recurring_weeks',
        'selected_days',
        'time_slots',
        'service_use_cases',
        'license_types',
        'material_ids',
        'service_requirements',
        'service_type'
    ];

    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
        'address_json' => 'array',
        'total_hours' => 'float',
        'total_amount' => 'float',
        'discount' => 'float',
        'tax' => 'float',
        'selected_days' => 'array',
        'time_slots' => 'array',

        'service_use_cases' => 'array',
        'license_types' => 'array',
        'material_ids' => 'array',
    ];

    protected $appends = [
        'license_type_details',
        'material_details',
        'service_use_case_details',
    ];
    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function getLicenseTypeDetailsAttribute()
    {
        return LicenseType::whereIn('id', $this->license_types ?? [])
            ->get(['id', 'name']);
    }

    public function getMaterialDetailsAttribute()
    {
        return MaterialType::whereIn('id', $this->material_ids ?? [])
            ->get(['id', 'name']);
    }

    public function getServiceUseCaseDetailsAttribute()
    {
        return ServiceUseCase::whereIn('id', $this->service_use_cases ?? [])
            ->get(['id', 'title']);
    }

    public function items()
    {
        return $this->hasMany(CartItem::class)->where('service_type', 0);
    }

    public function serviceItems()
    {
        return $this->hasMany(CartItem::class);
    }

    public function addonItems()
    {
        return $this->hasMany(CartItem::class)
                    ->where('service_type', 1);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function provider()
    {
        return $this->belongsTo(User::class, 'provider_id');
    }

    public function serviceCategory()
    {
        return $this->belongsTo(ServiceCategory::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function getFinalAmountAttribute()
    {
        return ($this->total_amount - $this->discount) + $this->tax;
    }

    //fetch user address
    public function address()
    {
        return $this->belongsTo(UserAddress::class, 'address_id', 'id');
    }
}
