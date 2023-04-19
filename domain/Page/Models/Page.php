<?php

declare(strict_types=1);

namespace Domain\Page\Models;

use AlexJustesen\FilamentSpatieLaravelActivitylog\Contracts\IsActivitySubject;
use Domain\Admin\Models\Admin;
use Domain\Support\MetaData\HasMetaData;
use Domain\Support\ConstraintsRelationships\Attributes\OnDeleteCascade;
use Domain\Support\ConstraintsRelationships\ConstraintsRelationships;
use Domain\Support\RouteUrl\Contracts\HasRouteUrl as HasRouteUrlContact;
use Domain\Support\RouteUrl\HasRouteUrl;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Domain\Support\MetaData\Contracts\HasMetaData as HasMetaDataContract;

/**
 * Domain\Page\Models\Page
 *
 * @property int $id
 * @property string $name
 * @property string $slug
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

#[OnDeleteCascade(['blockContents', 'metaData', 'routeUrls'])]
class Page extends Model implements IsActivitySubject, HasMetaDataContract, HasRouteUrlContact
{
    use LogsActivity;
    use HasSlug;
    use HasRouteUrl;
    use HasMetaData;
    use ConstraintsRelationships;

    protected $fillable = [
        'author_id',
        'name',
        'slug',
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

    /** @return HasMany<BlockContent> */
    public function blockContents(): HasMany
    {
        return $this->hasMany(BlockContent::class);
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

    public static function generateRouteUrl(Model $model, array $attributes): string
    {
        return Attribute::get(fn () => Blade::render(
            Blade::compileEchos($this->route_url),
            [
                'slug' => $this->slug,
            ]
        ));
    }

    /** @return BelongsTo<Admin, Page> */
    public function author(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'author_id');
    }
}
