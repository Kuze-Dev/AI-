<?php

declare(strict_types=1);

namespace Domain\Menu\Models;

use Domain\Menu\Enums\Target;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class Node extends Model implements Sortable
{
    use SortableTrait;

    protected $fillable = [
        'menu_id',
        'parent_id',
        'label',
        'target',
        'url',
        'order',
    ];

    protected $casts = [
        'target' => Target::class,
    ];

    protected $with = [
        'children',
    ];

    /** @return BelongsTo<Menu, Node> */
    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    /** @return HasMany<Node> */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /** @return Builder<Node> */
    public function buildSortQuery(): Builder
    {
        return static::query()->whereMenuId($this->menu_id)->whereParentId($this->parent_id);
    }
}
