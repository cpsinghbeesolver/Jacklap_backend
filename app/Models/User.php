<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;
use Digikraaft\ReviewRating\Traits\HasReviewRating;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
  use HasApiTokens, HasFactory, Notifiable, HasRoles, SoftDeletes, HasReviewRating;

  /**
   * The attributes that are mass assignable.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'name',
    'email',
    'password',
    'otp',
    'country_code',
    'phone',
    'otp_expires_at',
    'image',
    'dob',
    'gender',
    'profile_step',
    'email_verified_at',
    'deleted_at',
    'availability_status',
    'latitude',
    'longitude',
    'on_call_availability',
    'social_id',
    'is_active',
    'deactivated_at',
    'deactivated_by',
    'deactivation_reason',
  ];

  /**
   * The attributes that should be hidden for serialization.
   *
   * @var array<int, string>
   */
  protected $hidden = [
    'password',
    'remember_token',
  ];

  /**
   * Get the attributes that should be cast.
   *
   * @return array<string, string>
   */
  protected function casts(): array
  {
    return [
      'email_verified_at' => 'datetime',
      'password' => 'hashed',
    ];
  }

  protected $appends = ['average_rating', 'booking_count', 'provider_booking_count','image_url'];

  public function getImageAttribute($value)
  {
      if (!$value) {
          return null;
      }

      // return 'storage/' . $value;
      return $value;
  }

  public function professionalDetail(){
    return $this->hasOne(ProfessionalDetail::class);
  }
  
  public function services(){
    return $this->hasMany(Service::class);
  }

  public function media(){
    return $this->hasMany(Media::class);
  }

  public function bankDetail()
  {
    return $this->hasOne(BankDetail::class);
  }

  public function languages()
  {
    return $this->hasMany(UserLanguage::class,'user_id','id');
  }

  public function availabilitySlots()
  {
    return $this->hasMany(AvailabilitySlot::class);
  }

  public function getAverageRatingAttribute()
  {
      return number_format($this->reviews()->avg('rating') ?? 0, 1, '.', '');
  }

  public function reviews()
  {
    return $this->morphMany(\Digikraaft\ReviewRating\Models\Review::class, 'model');
  }

  public function getBookingCountAttribute()
  {
    return $this->bookings()->count();
  }

  public function getProviderBookingCountAttribute()
  {
      return $this->providerBookings()->count();
  }

  public function bookings()
  {
    return $this->hasMany(Booking::class, 'user_id');
  }

  // Bookings where user is PROVIDER
  public function providerBookings()
  {
    return $this->hasMany(Booking::class, 'provider_id');
  }

  public function addonServices()
  {
    return $this->hasMany(AddonService::class, 'user_id');
  }

  public function licenseTypes()
  {
      return $this->hasMany(UserLicenseType::class);
  }

  public function serviceUsecases()
  {
      return $this->hasMany(UserServiceUsecase::class);
  }

  public function providerMaterials()
  {
      return $this->hasMany(ProviderMaterial::class);
  }

  public function payments()
  {
      return $this->hasMany(Payment::class);
  }

  public function getImageUrlAttribute()
  {
      if (!$this->image) {
          return null;
      }

      return Storage::disk('s3')->exists($this->image)
          ? Storage::disk('s3')->temporaryUrl(
              $this->image,
              now()->addMinutes(30)
          )
          : null;
  }
}
