<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Models\BookingImage;

class Booking extends Model
{
    protected $fillable = [
        // ── Existing fields ──────────────────────────────────────────────
        'user_id',
        'provider_id',
        'service_category_id',
        'start_datetime',
        'end_datetime',
        'total_hours',
        'total_amount',
        'discount',
        'tax',
        'payable_amount',
        'address_id',
        'address_json',
        'booking_number',
        'status',
        'payment_method',
        'booking_start_time',
        'booking_end_time',
        'service_type',

        // ── Recurring / slot fields ──────────────────────────────────────
        'parent_booking_id',

        'slot_date',
        'slot_start_time',
        'slot_end_time',
        'slot_index',

        'duration_type',
        'is_recurring',
        'recurring_weeks',
        'selected_days',
        'time_slots',
        'transmission_type',
        'cancel_reason',
        'service_use_cases',
        'license_types',
        'material_ids',
        'service_requirements',
        'otp',
        'is_paid',
        'payment_status',
        'is_seeker_rated',
        'is_provider_rated',
        'expired_email_sent',
        'is_service_rated'
    ];

    protected $casts = [
        // ── Existing casts ───────────────────────────────────────────────
        'start_datetime' => 'datetime',
        'end_datetime'   => 'datetime',
        'address_json'   => 'array',

        'service_use_cases' => 'array',
        'license_types' => 'array',
        'material_ids' => 'array',
        // ── New casts ────────────────────────────────────────────────────
        'selected_days'  => 'array',
        'time_slots'     => 'array',
        'is_recurring'   => 'boolean',
        'slot_date'      => 'date:Y-m-d',
        'total_hours'    => 'float',
        'total_amount'   => 'float',
        'payable_amount' => 'float',
        'discount'       => 'float',
        'tax'            => 'float',
    ];

    protected $appends = [
        'license_type_details',
        'material_details',
        'service_use_case_details',
    ];

    public const STATUS_PENDING = 'pending';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_START_JOURNEY = 'start_journey';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

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

    // ── Existing ─────────────────────────────────────────────────────────

    public function items()
    {
        return $this->hasMany(BookingItem::class)->where('service_type', 0);
    }

    public function serviceItems()
    {
        return $this->hasMany(BookingItem::class);
    }

    public function concerns()
    {
        return $this->hasMany(BookingConcern::class);
    }

    public function addonItems()
    {
        return $this->hasMany(BookingItem::class)->where('service_type', 1);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function serviceCategory()
    {
        return $this->belongsTo(ServiceCategory::class);
    }

    public function provider()
    {
        return $this->belongsTo(User::class, 'provider_id');
    }

    // ── New ──────────────────────────────────────────────────────────────

    /** All child slot-bookings under this parent, ordered by slot position */
    public function childBookings()
    {
        return $this->hasMany(Booking::class, 'parent_booking_id')
                    ->orderBy('slot_index');
    }

    /** Parent booking this slot belongs to */
    public function parentBooking()
    {
        return $this->belongsTo(Booking::class, 'parent_booking_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    /** Only top-level (parent or single-day) bookings */
    public function scopeParentOnly($query)
    {
        return $query->whereNull('parent_booking_id');
    }

    /** Only child slot-bookings */
    public function scopeChildOnly($query)
    {
        return $query->whereNotNull('parent_booking_id');
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForProvider($query, int $providerId)
    {
        return $query->where('provider_id', $providerId);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function isParent(): bool
    {
        return is_null($this->parent_booking_id);
    }

    public function isChild(): bool
    {
        return !is_null($this->parent_booking_id);
    }

    public function isSingleDay(): bool
    {
        return $this->duration_type === 'single_day';
    }

    public function isRecurringBooking(): bool
    {
        return $this->is_recurring && $this->duration_type === 'multiple_days';
    }

    public function canBeStarted(): bool
    {
        return $this->status === 'pending';
    }

    public function canBeCompleted(): bool
    {
        return $this->status === 'in_progress';
    }

    public function canBeCancelled(): bool
    {
        return $this->status === 'pending';
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public function images()
    {
        return $this->hasMany(BookingImage::class);
    }
}