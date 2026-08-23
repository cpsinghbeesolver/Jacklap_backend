<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    protected $fillable = [
        'question',
        'answer',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function getStatusAttribute($value)
    {
        return $value ? 'Active' : 'In-Active';
    }
}
