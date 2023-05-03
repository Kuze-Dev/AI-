<?php

declare(strict_types=1);

namespace Domain\Content\Models;

use Domain\Admin\Models\Admin;
use Domain\Content\Models\Builders\ContentEntryBuilder;
use Domain\Support\MetaData\HasMetaData;
use Domain\Support\ConstraintsRelationships\Attributes\OnDeleteCascade;
use Domain\Support\ConstraintsRelationships\ConstraintsRelationships;
use Domain\Support\RouteUrl\Contracts\HasRouteUrl as HasRouteUrlContact;
use Domain\Support\RouteUrl\HasRouteUrl;
use Domain\Taxonomy\Models\TaxonomyTerm;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Domain\Support\MetaData\Contracts\HasMetaData as HasMetaDataContract;
use Illuminate\Support\Str;

/**
 * Domain\Content\Models\ContentEntry
 *
 * @property int $id
 * @property int $content_id
 * @property string $title
 * @property string $slug
 * @property \Illuminate\Support\Carbon|null $published_at
 * @property array $data
 * @property int|null $order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|Activity[] $activities
 * @property-read int|null $activities_count
 * @property-read \Domain\Content\Models\Content $content
 * @property-read \Domain\Support\MetaData\Models\MetaData $metaData
 * @property-read \Illuminate\Database\Eloquent\Collection|TaxonomyTerm[] $taxonomyTerms
 * @property-read int|null $taxonomy_terms_count
 * @property-read string|null $qualified_route_url
 * @method static ContentEntryBuilder|ContentEntry newModelQuery()
 * @method static ContentEntryBuilder|ContentEntry newQuery()
 * @method static ContentEntryBuilder|ContentEntry query()
 * @method static ContentEntryBuilder|ContentEntry whereContentId($value)
 * @method static ContentEntryBuilder|ContentEntry whereCreatedAt($value)
 * @method static ContentEntryBuilder|ContentEntry whereData($value)
 * @method static ContentEntryBuilder|ContentEntry whereId($value)
 * @method static ContentEntryBuilder|ContentEntry whereOrder($value)
 * @method static ContentEntryBuilder|ContentEntry wherePublishStatus(?\Domain\Content\Enums\PublishBehavior $publishBehavior = null, ?string $timezone = null)
 * @method static ContentEntryBuilder|ContentEntry wherePublishedAt($value)
 * @method static ContentEntryBuilder|ContentEntry whereSlug($value)
 * @method static ContentEntryBuilder|ContentEntry whereTitle($value)
 * @method static ContentEntryBuilder|ContentEntry whereUpdatedAt($value)
 * @mixin \Eloquent
 */
#[OnDeleteCascade(['taxonomyTerms', 'metaData', 'routeUrls'])]
class ContentEntry extends Model implements HasMetaDataContract, HasRouteUrlContact
{
    use LogsActivity;
    use HasSlug;
    use HasRouteUrl;
    use HasMetaData;
    use ConstraintsRelationships;

    /**
     * Declare columns
     * that are mass assignable.
     */
    protected $fillable = [
        'title',
        'data',
        'content_id',
        'taxonomy_term_id',
        'order',
        'author_id',
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
     * current model to contents.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Domain\Content\Models\Content, \Domain\Content\Models\ContentEntry>
     */
    public function content(): BelongsTo
    {
        return $this->belongsTo(Content::class);
    }

    /**
     * Declare relationship of
     * current model to contents.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<\Domain\Taxonomy\Models\TaxonomyTerm>
     */
    public function taxonomyTerms(): BelongsToMany
    {
        return $this->belongsToMany(TaxonomyTerm::class);
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

    /** @return ContentEntryBuilder<self> */
    public function newEloquentBuilder($query): ContentEntryBuilder
    {
        return new ContentEntryBuilder($query);
    }

    /** @param self $model */
    public static function generateRouteUrl(Model $model, array $attributes): string
    {
        return Str::start($model->content->prefix, '/') . Str::of($attributes['title'])->slug()->start('/');
    }

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Admin, ContentEntry> */
    public function author(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'author_id');
    }
}
