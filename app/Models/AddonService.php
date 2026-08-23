<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AddonService extends Model
{
    protected $table = 'addon_services';

    protected $fillable = [
        'user_id',
        'name',
        'type',
        'status',
        'service_category_id',
        'description',
        'is_default',
        'price',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'price' => 'decimal:2',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes (optional but useful)
    |--------------------------------------------------------------------------
    */

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }
}