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

/**
 * Domain\Menu\Models\Node
 *
 * @property int $id
 * @property string $label
 * @property int $menu_id
 * @property int|null $parent_id
 * @property string|null $url
 * @property Target $target
 * @property int $order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|Node[] $children
 * @property-read int|null $children_count
 * @property-read \Domain\Menu\Models\Menu|null $menu
 * @method static Builder|Node newModelQuery()
 * @method static Builder|Node newQuery()
 * @method static Builder|Node ordered(string $direction = 'asc')
 * @method static Builder|Node query()
 * @method static Builder|Node whereCreatedAt($value)
 * @method static Builder|Node whereId($value)
 * @method static Builder|Node whereLabel($value)
 * @method static Builder|Node whereMenuId($value)
 * @method static Builder|Node whereOrder($value)
 * @method static Builder|Node whereParentId($value)
 * @method static Builder|Node whereTarget($value)
 * @method static Builder|Node whereUpdatedAt($value)
 * @method static Builder|Node whereUrl($value)
 * @mixin \Eloquent
 */
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

    /** @return BelongsTo<Menu, Node> */
    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    /** @return HasMany<Node> */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->ordered()->with('children');
    }

    /** @return Builder<Node> */
    public function buildSortQuery(): Builder
    {
        return static::query()->whereMenuId($this->menu_id)->whereParentId($this->parent_id);
    }
}
