<?php

declare(strict_types=1);

namespace Domain\Menu\Models;

use Domain\Menu\Enums\NodeType;
use Domain\Menu\Enums\Target;
use Support\ConstraintsRelationships\Attributes\OnDeleteCascade;
use Support\ConstraintsRelationships\ConstraintsRelationships;
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
 * @property int $id
 * @property int $menu_id
 * @property int|null $parent_id
 * @property string|null $model_type
 * @property int|null $model_id
 * @property string $label
 * @property Target $target
 * @property NodeType $type
 * @property string|null $url
 * @property int $order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Node> $children
 * @property-read int|null $children_count
 * @property-read \Domain\Menu\Models\Menu|null $menu
 * @property-read Model|Eloquent|null $model
 * @method static Builder|Node newModelQuery()
 * @method static Builder|Node newQuery()
 * @method static Builder|Node ordered(string $direction = 'asc')
 * @method static Builder|Node query()
 * @method static Builder|Node whereCreatedAt($value)
 * @method static Builder|Node whereId($value)
 * @method static Builder|Node whereLabel($value)
 * @method static Builder|Node whereMenuId($value)
 * @method static Builder|Node whereModelId($value)
 * @method static Builder|Node whereModelType($value)
 * @method static Builder|Node whereOrder($value)
 * @method static Builder|Node whereParentId($value)
 * @method static Builder|Node whereTarget($value)
 * @method static Builder|Node whereType($value)
 * @method static Builder|Node whereUpdatedAt($value)
 * @method static Builder|Node whereUrl($value)
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
