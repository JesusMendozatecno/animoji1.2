<?php

namespace App\Services;

use App\Models\MediaProgress;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class ProgressService
{
    public function __construct(private readonly CatalogService $catalog)
    {
    }

    public function sync(User $user, string $type, int $id, int $position, float $percentage): MediaProgress
    {
        $this->catalog->model($type)::query()->findOrFail($id);

        $progress = $user->progress()->updateOrCreate(
            [
                'progressable_type' => $type,
                'progressable_id' => $id,
            ],
            [
                'position' => $position,
                'percentage' => $percentage,
                'completed_at' => $percentage >= 95 ? now() : null,
            ],
        );

        return $progress->load('progressable');
    }

    /**
     * @return Collection<int, MediaProgress>
     */
    public function forUser(User $user): Collection
    {
        return $user->progress()
            ->with('progressable')
            ->latest('updated_at')
            ->get();
    }
}
