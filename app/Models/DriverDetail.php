<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverDetail extends Model
{
    use HasFactory;

    protected $table = 'driver_details';

    protected $fillable = [
        'user_id',
        'service_usecase_id',
        'license_type_id',
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

    public function serviceUsecase()
    {
        return $this->belongsTo(ServiceUseCase::class, 'service_usecase_id', 'id');
    }
    
    public function licenseType()
    {
        return $this->belongsTo(LicenseType::class, 'license_type_id', 'id');
    }
}