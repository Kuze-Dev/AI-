<?php

declare(strict_types=1);

namespace Domain\Page\Models;

use AlexJustesen\FilamentSpatieLaravelActivitylog\Contracts\IsActivitySubject;
use Domain\Page\Models\Builders\PageBuilder;
use Domain\Support\MetaData\HasMetaData;
use Domain\Support\ConstraintsRelationships\Attributes\OnDeleteCascade;
use Domain\Support\ConstraintsRelationships\ConstraintsRelationships;
use Domain\Support\SlugHistory\HasSlugHistory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Blade;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Domain\Support\MetaData\Contracts\HasMetaData as HasMetaDataContract;

/**
 * Domain\Page\Models\Page
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $route_url
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|Activity[] $activities
 * @property-read int|null $activities_count
 * @property-read \Domain\Support\MetaData\Models\MetaData $metaData
 * @property-read \Illuminate\Database\Eloquent\Collection|\Domain\Page\Models\BlockContent[] $blockContents
 * @property-read int|null $block_contents_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\Domain\Support\SlugHistory\SlugHistory[] $slugHistories
 * @property-read int|null $slug_histories_count
 * @property-read string|null $qualified_route_url
 * @method static \Illuminate\Database\Eloquent\Builder|Page newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Page newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Page query()
 * @method static \Illuminate\Database\Eloquent\Builder|Page whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Page whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Page whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Page whereRouteUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Page whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Page whereUpdatedAt($value)
 * @mixin \Eloquent
 */

#[OnDeleteCascade(['blockContents', 'metaData'])]
class Page extends Model implements IsActivitySubject, HasMetaDataContract
{
    use LogsActivity;
    use HasSlug;
    use HasSlugHistory;
    use HasMetaData;
    use ConstraintsRelationships;

    protected $fillable = [
        'name',
        'slug',
        'route_url',
        'published_at',
    ];

    /**
     * Columns that are converted
     * to a specific data type.
     */
    protected $casts = [
        'published_at' => 'datetime',
    ];

    /**
     * Define default reference
     * for meta data properties.
     *
     * @return array
     */
    public function defaultMetaData(): array
    {
        return [
            'title' => $this->name,
        ];
    }

    /** @return PageBuilder<self> */
    public function newEloquentBuilder($query): PageBuilder
    {
        return new PageBuilder($query);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /** @return HasMany<BlockContent> */
    public function blockContents(): HasMany
    {
        return $this->hasMany(BlockContent::class);
    }

    public function getActivitySubjectDescription(Activity $activity): string
    {
        return 'Page: ' . $this->name;
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

    /** @return Attribute<string, static> */
    protected function qualifiedRouteUrl(): Attribute
    {
        return Attribute::get(fn () => Blade::render(
            Blade::compileEchos($this->route_url),
            [
                'slug' => $this->slug,
            ]
        ));
    }
}
