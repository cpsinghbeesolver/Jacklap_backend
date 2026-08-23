<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Digikraaft\ReviewRating\Traits\HasReviewRating;
use Illuminate\Support\Facades\Auth;

class ServiceCategory extends Model
{
    use HasReviewRating;
    protected $fillable = [
        'name',
        'slug',
        'price',
        'image',
        'status',
        'sort_order',
        'description'
    ];
    protected $appends = ['average_rating', 'booking_count'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    public function professionalDetails(){
        return $this->hasMany(ProfessionalDetail::class);
    }

    public function getImageAttribute($value){
        if(str_contains($value, 'frontend')){
            return $value;
        }else{
            return 'storage/'.$value;
        }
    }

    public function services()
    {
        return $this->hasMany(MasterService::class, 'service_category_id')->where('is_default',1);
    }

    public function materials()
    {
        return $this->hasMany(MaterialType::class, 'service_category_id');
    }

    public function reviews()
    {
        return $this->morphMany(\Digikraaft\ReviewRating\Models\Review::class, 'model');
    }

    public function getAverageRatingAttribute()
    {
        return number_format($this->reviews()->avg('rating') ?? 0, 1, '.', '');
    }

    public function getBookingCountAttribute()
    {
        return $this->bookings()->count();
    }
    public function bookings()
    {
        return $this->hasMany(Booking::class, 'service_category_id');
    }
}
