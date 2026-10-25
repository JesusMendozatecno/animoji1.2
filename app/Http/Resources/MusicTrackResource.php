<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MusicTrackResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'artist' => $this->artist,
            'album' => $this->album,
            'cover' => $this->cover,
            'audio_url' => $this->audio_url,
            'duration' => $this->duration,
            'release_year' => $this->release_year,
        ];
    }
}
