<?php

declare(strict_types=1);

namespace Domain\Menu\Models;

use Domain\Menu\Enums\NodeType;
use Domain\Menu\Enums\Target;
use Domain\Support\ConstraintsRelationships\Attributes\OnDeleteCascade;
use Domain\Support\ConstraintsRelationships\ConstraintsRelationships;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;
use Eloquent;

/**
 * Domain\Menu\Models\Node
 *
 * @property Target $target
 * @property NodeType $type
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Node> $children
 * @property-read int|null $children_count
 * @property-read \Domain\Menu\Models\Menu|null $menu
 * @property-read Model|Eloquent $model
 * @method static Builder|Node newModelQuery()
 * @method static Builder|Node newQuery()
 * @method static Builder|Node ordered(string $direction = 'asc')
 * @method static Builder|Node query()
 * @mixin Eloquent
 */
#[OnDeleteCascade(['children'])]
class Node extends Model implements Sortable
{
    use SortableTrait;
    use ConstraintsRelationships;

    protected $fillable = [
        'menu_id',
        'parent_id',
        'model_type',
        'model_id',
        'label',
        'target',
        'type',
        'url',
        'order',
    ];

    protected $with = [
        'model',
    ];

    protected $casts = [
        'target' => Target::class,
        'type' => NodeType::class,
    ];

    /** @return BelongsTo<Menu, self> */
    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    /** @return HasMany<self> */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->ordered()->with('children');
    }

    /** @return MorphTo<Model, self> */
    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    /** @return Builder<self> */
    public function buildSortQuery(): Builder
    {
        return static::query()->whereMenuId($this->menu_id)->whereParentId($this->parent_id);
    }
}
