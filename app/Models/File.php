<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
class File extends Model
{
    use HasFactory;
    protected $fillable = [
        'original_name',
        'path',
        'mime_type',
        'size',
        'fileable_id',
        'fileable_type',
        'is_primary',
        'sort_order'
    ];
    protected $appends = ['url'];

    public function fileable()
    {
        return $this->morphTo();
    }

    // Optional: generate full URL easily
    public function getUrlAttribute()
    {
        // return 'storage/' . $this->path;
        if (!$this->path) {
            return null;
        }

        // return Storage::disk('s3')->exists($this->path)
        //     ? Storage::disk('s3')->temporaryUrl(
        //         $this->path,
        //         now()->addMinutes(30)
        //     )
        //     : null;
    }

    public function getPathAttribute($value)
    {
        if (empty($value)) {
            return $value;
        }

        // If it's already a full URL (like from S3), don't touch it
        if (str_starts_with($value, 'http')) {
            return $value;
        }

        // Return with storage prefix
        return $value;
        
    }
}
