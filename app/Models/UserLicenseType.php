<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class UserLicenseType extends Model
{
    protected $fillable = [
        'user_id',
        'license_type_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function licenseType()
    {
        return $this->belongsTo(LicenseType::class);
    }
}
