<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceUseCase extends Model
{
    protected $fillable = [
        'service_category_id',
        'title'
    ];

    public function category()
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }
}