<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/** ➕ Elemento dentro de una lista personal. */
class ListItem extends Model
{
    protected $fillable = [
        'user_list_id',
        'itemable_type',
        'itemable_id',
        'position',
    ];

    protected function casts(): array
    {
        return ['position' => 'integer'];
    }

    public function list(): BelongsTo
    {
        return $this->belongsTo(UserList::class, 'user_list_id');
    }

    public function itemable(): MorphTo
    {
        return $this->morphTo();
    }
}
