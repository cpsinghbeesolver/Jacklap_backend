<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserServiceUsecase extends Model
{
    protected $fillable = [
        'user_id',
        'service_usecase_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function serviceUseCase()
    {
        return $this->belongsTo(ServiceUseCase::class,'service_usecase_id','id');
    }
}
