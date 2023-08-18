<?php

declare(strict_types=1);

namespace Domain\Page\Models;

use Illuminate\Support\Str;
use Domain\Site\Traits\Sites;
use Spatie\Sluggable\HasSlug;
use Domain\Admin\Models\Admin;
use Domain\Page\Enums\Visibility;
use Spatie\Sluggable\SlugOptions;
use Support\MetaData\HasMetaData;
use Support\RouteUrl\HasRouteUrl;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use Domain\Page\Models\Builders\PageBuilder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Support\ConstraintsRelationships\ConstraintsRelationships;
use Support\ConstraintsRelationships\Attributes\OnDeleteCascade;
use Support\RouteUrl\Contracts\HasRouteUrl as HasRouteUrlContact;
use Support\MetaData\Contracts\HasMetaData as HasMetaDataContract;

/**
 * Domain\Page\Models\Page
 *
 * @property int $id
 * @property int|null $author_id
 * @property string $name
 * @property string $locale
 * @property string $slug
 * @property Visibility $visibility
 * @property \Illuminate\Support\Carbon|null $published_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Support\RouteUrl\Models\RouteUrl|null $activeRouteUrl
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @property-read Admin|null $author
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Domain\Page\Models\BlockContent> $blockContents
 * @property-read int|null $block_contents_count
 * @property-read \Support\MetaData\Models\MetaData|null $metaData
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Support\RouteUrl\Models\RouteUrl> $routeUrls
 * @property-read int|null $route_urls_count
 * @method static PageBuilder|Page newModelQuery()
 * @method static PageBuilder|Page newQuery()
 * @method static PageBuilder|Page query()
 * @method static PageBuilder|Page whereAuthorId($value)
 * @method static PageBuilder|Page whereCreatedAt($value)
 * @method static PageBuilder|Page whereId($value)
 * @method static PageBuilder|Page whereName($value)
 * @method static PageBuilder|Page wherePublishedAt($value)
 * @method static PageBuilder|Page wherePublishedAtRange(?\Carbon\Carbon $publishedAtStart = null, ?\Carbon\Carbon $publishedAtEnd = null)
 * @method static PageBuilder|Page wherePublishedAtYearMonth(int $year, ?int $month = null)
 * @method static PageBuilder|Page whereSlug($value)
 * @method static PageBuilder|Page whereUpdatedAt($value)
 * @method static PageBuilder|Page whereVisibility($value)
 * @mixin \Eloquent
 */

#[OnDeleteCascade(['blockContents', 'metaData', 'routeUrls'])]
class Page extends Model implements HasMetaDataContract, HasRouteUrlContact
{
    use LogsActivity;
    use HasSlug;
    use HasRouteUrl;
    use HasMetaData;
    use ConstraintsRelationships;
    use Sites;

    protected $fillable = [
        'author_id',
        'name',
        'visibility',
        'published_at',
        'locale',
    ];

    /**
     * Columns that are converted
     * to a specific data type.
     */
    protected $casts = [
        'visibility' => Visibility::class,
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

    public static function generateRouteUrl(Model $model, array $attributes): string
    {
        return Str::of($attributes['name'])->slug()->start('/')->toString();
    }

    /** @return BelongsTo<Admin, Page> */
    public function author(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'author_id');
    }

    public function isPublished(): bool
    {
        return is_null($this->published_at);
    }

    public function isHomePage(): bool
    {
        return $this->slug === 'home';
    }
}
