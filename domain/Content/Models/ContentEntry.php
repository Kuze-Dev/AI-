<?php

declare(strict_types=1);

namespace Domain\Content\Models;

use Domain\Admin\Models\Admin;
use Domain\Blueprint\Models\BlueprintData;
use Domain\Content\Models\Builders\ContentEntryBuilder;
use Domain\Internationalization\Concerns\HasInternationalizationInterface;
use Domain\Site\Traits\Sites;
use Domain\Taxonomy\Models\TaxonomyTerm;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
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
 * Domain\Content\Models\ContentEntry
 *
 * @property int $id
 * @property int|null $author_id
 * @property int $content_id
 * @property string $title
 * @property string $slug
 * @property string $locale
 * @property string|null $draftable_id
 * @property \Illuminate\Support\Carbon|null $published_at
 * @property array $data
 * @property bool $status
 * @property int|null $order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Support\RouteUrl\Models\RouteUrl|null $activeRouteUrl
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @property-read Admin|null $author
 * @property-read \Domain\Content\Models\Content $content
 * @property-read \Support\MetaData\Models\MetaData|null $metaData
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Support\RouteUrl\Models\RouteUrl> $routeUrls
 * @property-read int|null $route_urls_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, TaxonomyTerm> $taxonomyTerms
 * @property-read int|null $taxonomy_terms_count
 * @property-read ContentEntry|null $pageDraft
 *
 * @method static ContentEntryBuilder|ContentEntry newModelQuery()
 * @method static ContentEntryBuilder|ContentEntry newQuery()
 * @method static ContentEntryBuilder|ContentEntry query()
 * @method static ContentEntryBuilder|ContentEntry whereAuthorId($value)
 * @method static ContentEntryBuilder|ContentEntry whereContentId($value)
 * @method static ContentEntryBuilder|ContentEntry whereCreatedAt($value)
 * @method static ContentEntryBuilder|ContentEntry whereData($value)
 * @method static ContentEntryBuilder|ContentEntry whereId($value)
 * @method static ContentEntryBuilder|ContentEntry whereOrder($value)
 * @method static ContentEntryBuilder|ContentEntry wherePublishStatus(?\Domain\Content\Enums\PublishBehavior $publishBehavior = null, ?string $timezone = null)
 * @method static ContentEntryBuilder|ContentEntry wherePublishedAt($value)
 * @method static ContentEntryBuilder|ContentEntry wherePublishedAtRange(?\Illuminate\Support\Carbon $publishedAtStart = null, ?\Illuminate\Support\Carbon $publishedAtEnd = null)
 * @method static ContentEntryBuilder|ContentEntry wherePublishedAtYearMonth(int $year, ?int $month = null)
 * @method static ContentEntryBuilder|ContentEntry whereSlug($value)
 * @method static ContentEntryBuilder|ContentEntry whereTaxonomyTerms(string $taxonomy, array $terms)
 * @method static ContentEntryBuilder|ContentEntry whereTitle($value)
 * @method static ContentEntryBuilder|ContentEntry whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[OnDeleteCascade(['taxonomyTerms', 'metaData', 'routeUrls', 'blueprintData'])]
class ContentEntry extends Model implements HasInternationalizationInterface, HasMetaDataContract, HasRouteUrlContact
{
    use ConstraintsRelationships;
    use HasMetaData;
    use HasRouteUrl;
    use HasSlug;
    use LogsActivity;
    use Sites;

    /**
     * Declare columns
     * that are mass assignable.
     */
    protected $fillable = [
        'title',
        'data',
        'content_id',
        'order',
        'author_id',
        'published_at',
        'locale',
        'status',
        'translation_id',
    ];

    protected $with = [
        'sites',
    ];

    /**
     * Columns that are converted
     * to a specific data type.
     */
    protected function casts(): array
    {
        return [
            'data' => 'array',
            'published_at' => 'datetime',
        ];
    }
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Define default reference
     * for meta data properties.
     */
    #[\Override]
    public function defaultMetaData(): array
    {
        return [
            'title' => $this->title,
        ];
    }

    //create a name Attribute For title field if draftable_id is not null add (Draft) to the end of the title
    public function getNameAttribute(): string
    {
        return $this->draftable_id ? $this->title.' (Draft)' : $this->title;
    }

    /**
     * Declare relationship of
     * current model to contents.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Domain\Content\Models\Content, $this>
     */
    public function content(): BelongsTo
    {
        return $this->belongsTo(Content::class);
    }

    /** @return \Illuminate\Database\Eloquent\Relations\MorphMany<\Domain\Blueprint\Models\BlueprintData, $this> */
    public function blueprintData(): MorphMany
    {
        return $this->morphMany(BlueprintData::class, 'model');
    }

    /**
     * Declare relationship of
     * current model to contents.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<\Domain\Taxonomy\Models\TaxonomyTerm, $this>
     */
    public function taxonomyTerms(): BelongsToMany
    {
        return $this->belongsToMany(TaxonomyTerm::class);
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->preventOverwrite()
            ->doNotGenerateSlugsOnUpdate()
            ->saveSlugsTo($this->getRouteKeyName());
    }

    /**
     * Set the column reference
     * for route keys.
     */
    #[\Override]
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /** @return ContentEntryBuilder<self> */
    #[\Override]
    public function newEloquentBuilder($query): ContentEntryBuilder
    {
        return new ContentEntryBuilder($query);
    }

    /** @param  self  $model */
    #[\Override]
    public static function generateRouteUrl(Model $model, array $attributes): string
    {
        return Str::start($model->content->prefix, '/').Str::of($attributes['title'])->slug()->start('/');
    }

    /** @return \Illuminate\Database\Eloquent\Relations\HasOne<\Domain\Content\Models\ContentEntry, $this> */
    public function pageDraft(): HasOne
    {
        return $this->hasOne(ContentEntry::class, 'draftable_id');
    }

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Domain\Content\Models\ContentEntry, $this> */
    public function parentPage(): BelongsTo
    {
        return $this->belongsTo(ContentEntry::class, 'draftable_id');
    }

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Domain\Admin\Models\Admin, $this> */
    public function author(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'author_id');
    }

    /** @return \Illuminate\Database\Eloquent\Relations\HasMany<\Domain\Content\Models\ContentEntry, $this> */
    public function dataTranslation(): HasMany
    {
        return $this->hasMany(self::class, 'translation_id');
    }

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Domain\Content\Models\ContentEntry, $this> */
    public function parentTranslation(): BelongsTo
    {
        return $this->belongsTo(self::class, 'translation_id');
    }
}
