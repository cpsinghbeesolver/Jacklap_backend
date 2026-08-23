<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfessionalDetail extends Model
{

    protected $fillable = [
        'price',
        'experience',
        'languages',
        'tools',
        'skills',
        'user_id',
        'service_category_id',
        'transmission_type',
        'safety_record',
        'teaching_mode'
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function serviceCategory(){
        return $this->belongsTo(ServiceCategory::class);
    }

    public function licenseTypes()
    {
        return $this->hasMany(UserLicenseType::class, 'user_id', 'user_id'); 
    }

    public function serviceUseCases()
    {
        return $this->hasMany(UserServiceUsecase::class, 'user_id', 'user_id'); 
    }

    public function providerMaterials()
    {
        return $this->hasMany(ProviderMaterial::class, 'user_id', 'user_id');
    }
}
