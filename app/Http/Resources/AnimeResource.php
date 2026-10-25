<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnimeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'synopsis' => $this->synopsis,
            'cover' => $this->cover,
            'studio' => $this->studio,
            'year' => $this->year,
            'status' => $this->status,
            'rating' => $this->rating,
        ];
    }
}
