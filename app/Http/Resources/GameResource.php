<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GameResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'cover' => $this->cover,
            'platforms' => $this->platforms,
            'requirements' => $this->requirements,
            'download_url' => $this->download_url,
            'release_year' => $this->release_year,
            'rating' => $this->rating,
        ];
    }
}
