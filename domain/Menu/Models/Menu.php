<?php

declare(strict_types=1);

namespace Domain\Menu\Models;

use Support\ConstraintsRelationships\Attributes\OnDeleteCascade;
use Support\ConstraintsRelationships\ConstraintsRelationships;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Domain\Site\Traits\Sites;

/**
 * Domain\Menu\Models\Menu
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $locale
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Domain\Menu\Models\Node> $nodes
 * @property-read int|null $nodes_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Domain\Menu\Models\Node> $parentNodes
 * @property-read int|null $parent_nodes_count
 * @method static \Illuminate\Database\Eloquent\Builder|Menu newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Menu newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Menu query()
 * @method static \Illuminate\Database\Eloquent\Builder|Menu whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Menu whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Menu whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Menu whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Menu whereUpdatedAt($value)
 * @mixin \Eloquent
 */
#[OnDeleteCascade(['parentNodes'])]
class Menu extends Model
{
    use HasSlug;
    use LogsActivity;
    use ConstraintsRelationships;
    use Sites;

    protected $fillable = [
        'name',
        'slug',
        'locale',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /** @return HasMany<Node> */
    public function parentNodes(): HasMany
    {
        return $this->nodes()->whereNull('parent_id')->ordered();
    }

    /** @return HasMany<Node> */
    public function nodes(): HasMany
    {
        return $this->hasMany(Node::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->preventOverwrite()
            ->doNotGenerateSlugsOnUpdate()
            ->saveSlugsTo($this->getRouteKeyName());
    }
}
