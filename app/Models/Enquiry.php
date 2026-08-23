<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Enquiry extends Model
{
    protected $fillable = [
        'enquiry_number',
        'status',

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
        'service_type',
    ];

    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
        'address_json' => 'array',
        'total_hours' => 'float',
        'total_amount' => 'float',
        'discount' => 'float',
        'tax' => 'float',
        'is_recurring' => 'boolean',
        'selected_days' => 'array',
        'time_slots' => 'array',

        'service_use_cases' => 'array',
        'license_types' => 'array',
        'material_ids' => 'array',

        'status' => 'integer',
    ];

    protected $appends = [
        'license_type_details',
        'material_details',
        'service_use_case_details',
    ];

    /*
    |--------------------------------------------------------------------------
    | Status constants
    |--------------------------------------------------------------------------
    */

    public const STATUS_PENDING   = 0;
    public const STATUS_RESPONDED = 1;
    public const STATUS_CLOSED    = 2;

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
        return $this->hasMany(EnquiryItem::class)->where('service_type', 0);
    }

    public function serviceItems()
    {
        return $this->hasMany(EnquiryItem::class);
    }

    public function addonItems()
    {
        return $this->hasMany(EnquiryItem::class)
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

    public function address()
    {
        return $this->belongsTo(UserAddress::class, 'address_id', 'id');
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

    public function isPending(): bool
    {
        return (int) $this->status === self::STATUS_PENDING;
    }

    public function isResponded(): bool
    {
        return (int) $this->status === self::STATUS_RESPONDED;
    }

    public function isClosed(): bool
    {
        return (int) $this->status === self::STATUS_CLOSED;
    }

    /*
    |--------------------------------------------------------------------------
    | Boot (auto-generate enquiry_number)
    |--------------------------------------------------------------------------
    */

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($enquiry) {
            if (empty($enquiry->enquiry_number)) {
                $enquiry->enquiry_number = 'ENQ-' . now()->timestamp . '-' . strtoupper(\Illuminate\Support\Str::random(4));
            }
        });
    }
}