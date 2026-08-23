<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterService extends Model
{
    use HasFactory;

    protected $table = 'master_services';

    protected $fillable = [
        'name',
        'service_category_id',
        'description',
        'is_default',
        'type',
        'status',
        'price_limit',
        'subject_type',
        'input_type',
    ];

    const ACTIVE = 1;
    const INACTIVE = 0;

    const SUBJECT_ACADEMIC     = 1;
    const SUBJECT_NON_ACADEMIC = 2;
    const SUBJECT_MALE         = 3;
    const SUBJECT_FEMALE       = 4;
    const SUBJECT_BOTH         = 5;

    /**
     * Relationship with ServiceCategory
     */
    public function category()
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }

    /**
     * Relationship with User Services (if needed)
     */
    public function userServices()
    {
        return $this->hasMany(Service::class, 'service_id');
    }

    public function items()
    {
        return $this->hasMany(MasterServiceItem::class);
    }
}