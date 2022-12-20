<?php

declare(strict_types=1);

namespace Domain\Menu\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class Node extends Model implements Sortable
{
    use SortableTrait;

    protected $fillable = [
        'name',
        'menu_id',
        'parent_id',
        'url',
        'target',
        'sort',
    ];

    protected $with = [
        'childs',
    ];

    public array $sortable = [
        'order_column_name' => 'sort',
        'sort_when_creating' => true,
    ];

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Domain\Menu\Models\Menu, \Domain\Menu\Models\Node> */
    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class, 'menu_id', 'id');
    }

    /** @return HasMany<Node> */
    public function childs(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id', 'id');
    }
}
