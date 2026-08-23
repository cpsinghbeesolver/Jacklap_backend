<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LicenseType extends Model
{
    protected $table = 'license_types';

    protected $fillable = [
        'name',
        'description',
    ];

    // Optional: if you want to hide timestamps in API
    // protected $hidden = ['created_at', 'updated_at'];
}