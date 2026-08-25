<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['platform_fee','cancellation_charges','platform_fee_type','cancellation_charges_type'];
}
