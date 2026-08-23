<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IdentityType extends Model
{
    protected $fillable = [
        'name',
        'total_documents',
        'is_required'
    ];

    public function media(){
        return $this->hasMany(Media::class);
    }
}
