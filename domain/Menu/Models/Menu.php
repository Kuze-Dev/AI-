<?php

declare(strict_types=1);

namespace Domain\Menu\Models;

use Domain\Internationalization\Concerns\HasInternationalizationInterface;
use Domain\Site\Traits\Sites;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Support\ConstraintsRelationships\Attributes\OnDeleteCascade;
use Support\ConstraintsRelationships\ConstraintsRelationships;

/**
 * Domain\Menu\Models\Menu
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $locale
 * @property string $translation_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Domain\Menu\Models\Node> $nodes
 * @property-read int|null $nodes_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Domain\Menu\Models\Node> $parentNodes
 * @property-read int|null $parent_nodes_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Menu newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Menu newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Menu query()
 * @method static \Illuminate\Database\Eloquent\Builder|Menu whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Menu whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Menu whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Menu whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Menu whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[OnDeleteCascade(['parentNodes', 'dataTranslation'])]
class Menu extends Model implements HasInternationalizationInterface
{
    use ConstraintsRelationships;
    use HasSlug;
    use LogsActivity;
    use Sites;

    protected $fillable = [
        'name',
        'slug',
        'locale',
        'translation_id',
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

    #[\Override]
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /** @return HasMany<Menu> */
    public function dataTranslation(): HasMany
    {
        return $this->hasMany(self::class, 'translation_id');
    }

    /** @return BelongsTo<Menu, Menu> */
    public function parentTranslation(): BelongsTo
    {
        return $this->belongsTo(self::class, 'translation_id');
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
