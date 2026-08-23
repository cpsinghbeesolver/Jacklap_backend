<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserLanguage extends Model
{
    protected $fillable = [
        'user_id',
        'language',
        'proficiency',
        'language_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function language()
    {
        return $this->belongsTo(Language::class);
    }
}
