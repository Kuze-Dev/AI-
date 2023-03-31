<?php

declare(strict_types=1);

namespace Domain\Collection\Models;

use AlexJustesen\FilamentSpatieLaravelActivitylog\Contracts\IsActivitySubject;
use Domain\Collection\Models\Builders\CollectionEntryBuilder;
use Domain\Support\MetaData\HasMetaData;
use Domain\Support\ConstraintsRelationships\Attributes\OnDeleteCascade;
use Domain\Support\ConstraintsRelationships\ConstraintsRelationships;
use Domain\Support\RouteUrl\Contracts\HasRouteUrl as HasRouteUrlContract;
use Domain\Support\RouteUrl\HasRouteUrl;
use Domain\Taxonomy\Models\TaxonomyTerm;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Sluggable\HasSlug;
use Illuminate\Support\Facades\Blade;
use Spatie\Sluggable\SlugOptions;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Domain\Support\MetaData\Contracts\HasMetaData as HasMetaDataContract;

/**
 * Domain\Collection\Models\CollectionEntry
 *
 * @property int $id
 * @property int $collection_id
 * @property string $title
 * @property string $slug
 * @property \Illuminate\Support\Carbon|null $published_at
 * @property array $data
 * @property int|null $order
 * @property string $route_url Manage by RouteUrl Domain
 * @property bool $route_url_is_override Manage by RouteUrl Domain
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @property-read \Domain\Collection\Models\Collection|null $collection
 * @property-read \Domain\Support\MetaData\Models\MetaData|null $metaData
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Domain\Support\RouteUrl\Models\RouteUrl> $routeUrls
 * @property-read int|null $route_urls_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, TaxonomyTerm> $taxonomyTerms
 * @property-read int|null $taxonomy_terms_count
 * @method static CollectionEntryBuilder|CollectionEntry newModelQuery()
 * @method static CollectionEntryBuilder|CollectionEntry newQuery()
 * @method static CollectionEntryBuilder|CollectionEntry query()
 * @method static CollectionEntryBuilder|CollectionEntry whereCollectionId($value)
 * @method static CollectionEntryBuilder|CollectionEntry whereCreatedAt($value)
 * @method static CollectionEntryBuilder|CollectionEntry whereData($value)
 * @method static CollectionEntryBuilder|CollectionEntry whereId($value)
 * @method static CollectionEntryBuilder|CollectionEntry whereOrder($value)
 * @method static CollectionEntryBuilder|CollectionEntry wherePublishStatus(?\Domain\Collection\Enums\PublishBehavior $publishBehavior = null, ?string $timezone = null)
 * @method static CollectionEntryBuilder|CollectionEntry wherePublishedAt($value)
 * @method static CollectionEntryBuilder|CollectionEntry wherePublishedAtRange(?\Carbon\Carbon $publishedAtStart = null, ?\Carbon\Carbon $publishedAtEnd = null)
 * @method static CollectionEntryBuilder|CollectionEntry wherePublishedAtYearMonth(int $year, ?int $month = null)
 * @method static CollectionEntryBuilder|CollectionEntry whereRouteUrl($value)
 * @method static CollectionEntryBuilder|CollectionEntry whereRouteUrlIsOverride($value)
 * @method static CollectionEntryBuilder|CollectionEntry whereSlug($value)
 * @method static CollectionEntryBuilder|CollectionEntry whereTaxonomyTerms(string $taxonomy, array $terms)
 * @method static CollectionEntryBuilder|CollectionEntry whereTitle($value)
 * @method static CollectionEntryBuilder|CollectionEntry whereUpdatedAt($value)
 * @mixin \Eloquent
 */
#[OnDeleteCascade(['taxonomyTerms', 'metaData', 'routeUrls'])]
class CollectionEntry extends Model implements IsActivitySubject, HasMetaDataContract, HasRouteUrlContract
{
    use LogsActivity;
    use HasSlug;
    use HasMetaData;
    use ConstraintsRelationships;
    use HasRouteUrl;

    /**
     * Declare columns
     * that are mass assignable.
     */
    protected $fillable = [
        'title',
        'slug',
        'data',
        'collection_id',
        'taxonomy_term_id',
        'order',
        'published_at',
    ];

    /**
     * Columns that are converted
     * to a specific data type.
     */
    protected $casts = [
        'data' => 'array',
        'published_at' => 'datetime',
    ];

    /** @return LogOptions */
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
     *
     * @return array
     */
    public function defaultMetaData(): array
    {
        return [
            'title' => $this->title,
        ];
    }

    /**
     * Declare relationship of
     * current model to collections.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Domain\Collection\Models\Collection, \Domain\Collection\Models\CollectionEntry>
     */
    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }

    /**
     * Declare relationship of
     * current model to collections.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<\Domain\Taxonomy\Models\TaxonomyTerm>
     */
    public function taxonomyTerms(): BelongsToMany
    {
        return $this->belongsToMany(TaxonomyTerm::class);
    }

    /** Specify activity log description. */
    public function getActivitySubjectDescription(Activity $activity): string
    {
        return 'Collection Entry: '.$this->id;
    }

    /** @return SlugOptions */
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
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /** @return CollectionEntryBuilder<self> */
    public function newEloquentBuilder($query): CollectionEntryBuilder
    {
        return new CollectionEntryBuilder($query);
    }

      /** @return Attribute<string, static> */
      protected function qualifiedRouteUrl(): Attribute
      {
          return Attribute::get(fn () => Blade::render(
              Blade::compileEchos($this->collection->route_url .'/'.$this->slug),
              [
                  'slug' => $this->slug,
              ]
          ));
      }

    public function getRouteUrlDefaultUrl(): string
    {
        return $this->{$this->getSlugOptions()->slugField};
    }
}
