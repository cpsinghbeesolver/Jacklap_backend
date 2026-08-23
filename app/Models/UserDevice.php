<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserDevice extends Model
{
    protected $table = "user_devices";
    protected $guarded = [];
    protected $fillable = ['device_token','device_name','device_type','user_id'];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
