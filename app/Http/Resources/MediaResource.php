<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class MediaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'identity_type_id' => $this->identity_type_id,
            'user_id' => $this->user_id,
            'id_front' => $this->id_front ? url($this->id_front) : null,
            'id_back' => $this->id_back ? url($this->id_back) : null,
            'profile_photo' => $this->profile_photo ? url($this->profile_photo) : null,
            
            'certificates' => $this->certificates->map(function($file) {
                return [
                    'id' => $file->id,
                    'url' => url($file->path), // Assuming your File model has 'path'
                ];
            }),
            
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
