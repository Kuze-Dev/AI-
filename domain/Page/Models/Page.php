<?php

declare(strict_types=1);

namespace Domain\Page\Models;

use Domain\Admin\Models\Admin;
use Domain\Internationalization\Concerns\HasInternationalizationInterface;
use Domain\Page\Enums\Visibility;
use Domain\Page\Models\Builders\PageBuilder;
use Domain\Site\Traits\Sites;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Support\ConstraintsRelationships\Attributes\OnDeleteCascade;
use Support\ConstraintsRelationships\ConstraintsRelationships;
use Support\MetaData\Contracts\HasMetaData as HasMetaDataContract;
use Support\MetaData\HasMetaData;
use Support\RouteUrl\Contracts\HasRouteUrl as HasRouteUrlContact;
use Support\RouteUrl\HasRouteUrl;

/**
 * Domain\Page\Models\Page
 *
 * @property int $id
 * @property int|null $author_id
 * @property string $name
 * @property string $slug
 * @property Visibility $visibility
 * @property string|null $draftable_id
 * @property \Illuminate\Support\Carbon|null $published_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $locale
 * @property-read \Support\RouteUrl\Models\RouteUrl|null $activeRouteUrl
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @property-read Admin|null $author
 * @property-read Page|null $pageDraft
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Domain\Page\Models\BlockContent> $blockContents
 * @property-read int|null $block_contents_count
 * @property-read \Support\MetaData\Models\MetaData|null $metaData
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Support\RouteUrl\Models\RouteUrl> $routeUrls
 * @property-read int|null $route_urls_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Domain\Site\Models\Site> $sites
 * @property-read int|null $sites_count
 *
 * @method static PageBuilder|Page newModelQuery()
 * @method static PageBuilder|Page newQuery()
 * @method static PageBuilder|Page query()
 * @method static PageBuilder|Page whereAuthorId($value)
 * @method static PageBuilder|Page whereCreatedAt($value)
 * @method static PageBuilder|Page whereId($value)
 * @method static PageBuilder|Page whereLocale($value)
 * @method static PageBuilder|Page whereName($value)
 * @method static PageBuilder|Page wherePublishedAt($value)
 * @method static PageBuilder|Page wherePublishedAtRange(?\Illuminate\Support\Carbon $publishedAtStart = null, ?\Illuminate\Support\Carbon $publishedAtEnd = null)
 * @method static PageBuilder|Page wherePublishedAtYearMonth(int $year, ?int $month = null)
 * @method static PageBuilder|Page whereSlug($value)
 * @method static PageBuilder|Page whereUpdatedAt($value)
 * @method static PageBuilder|Page whereVisibility($value)
 *
 * @mixin \Eloquent
 */
#[OnDeleteCascade(['blockContents', 'metaData', 'routeUrls'])]
class Page extends Model implements HasInternationalizationInterface, HasMetaDataContract, HasRouteUrlContact
{
    use ConstraintsRelationships;
    use HasMetaData;
    use HasRouteUrl;
    use HasSlug;
    use LogsActivity;
    use Sites;

    protected $fillable = [
        'author_id',
        'name',
        'visibility',
        'published_at',
        'locale',
        'draftable_id',
        'translation_id',
    ];

    protected $with = [
        'pageDraft',
    ];

    /**
     * Columns that are converted
     * to a specific data type.
     */
    protected function casts(): array
    {
        return [
            'visibility' => Visibility::class,
            'published_at' => 'datetime',
        ];
    }

    /**
     * Define default reference
     * for meta data properties.
     */
    #[\Override]
    public function defaultMetaData(): array
    {
        return [
            'title' => $this->name,
        ];
    }

    //create a titleAttribute for name field
    public function getTitleAttribute(): string
    {
        return $this->draftable_id ? $this->name.' (Draft)' : $this->name;
    }

    /** @return PageBuilder<self> */
    #[\Override]
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

    /** @return \Illuminate\Database\Eloquent\Relations\HasMany<\Domain\Page\Models\BlockContent, $this> */
    public function blockContents(): HasMany
    {
        return $this->hasMany(BlockContent::class);
    }

    #[\Override]
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /** @return \Illuminate\Database\Eloquent\Relations\HasOne<\Domain\Page\Models\Page, $this> */
    public function pageDraft(): HasOne
    {
        return $this->hasOne(Page::class, 'draftable_id');
    }

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Domain\Page\Models\Page, $this> */
    public function parentPage(): BelongsTo
    {
        return $this->belongsTo(Page::class, 'draftable_id');
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->preventOverwrite()
            ->doNotGenerateSlugsOnUpdate()
            ->saveSlugsTo($this->getRouteKeyName());
    }

    #[\Override]
    public static function generateRouteUrl(Model $model, array $attributes): string
    {
        return Str::of($attributes['name'])->slug()->start('/')->toString();
    }

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Domain\Admin\Models\Admin, $this> */
    public function author(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'author_id');
    }

    /** @return \Illuminate\Database\Eloquent\Relations\HasMany<\Domain\Page\Models\Page, $this> */
    public function dataTranslation(): HasMany
    {
        return $this->hasMany(self::class, 'translation_id');
    }

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Domain\Page\Models\Page, $this> */
    public function parentTranslation(): BelongsTo
    {
        return $this->belongsTo(self::class, 'translation_id');
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
