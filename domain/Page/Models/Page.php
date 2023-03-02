<?php

declare(strict_types=1);

namespace Domain\Page\Models;

use Domain\Site\Models\Site;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Spatie\Activitylog\LogOptions;
use Illuminate\Support\Facades\Blade;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;
use Domain\Support\MetaData\HasMetaData;
use Spatie\Activitylog\Traits\LogsActivity;
use Domain\Support\SlugHistory\HasSlugHistory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Domain\Support\ConstraintsRelationships\ConstraintsRelationships;
use Domain\Support\ConstraintsRelationships\Attributes\OnDeleteCascade;
use Domain\Support\MetaData\Contracts\HasMetaData as HasMetaDataContract;
use AlexJustesen\FilamentSpatieLaravelActivitylog\Contracts\IsActivitySubject;

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
 * @property-read \Illuminate\Database\Eloquent\Collection|\Domain\Page\Models\SliceContent[] $sliceContents
 * @property-read int|null $slice_contents_count
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

#[OnDeleteCascade(['sliceContents', 'metaData'])]
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

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /** @return HasMany<SliceContent> */
    public function sliceContents(): HasMany
    {
        return $this->hasMany(SliceContent::class);
    }

    /**
     * Declare relationship of
     * current model to site.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<\Domain\Site\Models\Site>
     */
    public function sites(): BelongsToMany
    {
        return $this->belongsToMany(Site::class);
    }

    public function getActivitySubjectDescription(Activity $activity): string
    {
        return 'Page: '.$this->name;
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
