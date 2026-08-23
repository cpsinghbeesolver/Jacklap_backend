<?php

namespace App\Models\ChatModule;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class MessageAttachment extends Model
{
    protected $appends = ['image_url'];
    protected $fillable = [
        'message_id',
        'path',
        'original_name',
        'mime_type',
        'size',
    ];

    public function message()
    {
        return $this->belongsTo(Message::class);
    }

    public function getImageUrlAttribute()
    {
        if (!$this->path) {
            return null;
        }

        return Storage::disk('s3')->exists($this->path)
            ? Storage::disk('s3')->temporaryUrl(
                $this->path,
                now()->addMinutes(30)
            )
            : null;
    }
}
