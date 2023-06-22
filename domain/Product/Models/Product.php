<?php

declare(strict_types=1);

namespace Domain\Product\Models;

use Domain\Support\ConstraintsRelationships\Attributes\OnDeleteCascade;
use Domain\Support\MetaData\HasMetaData;
use Domain\Support\ConstraintsRelationships\ConstraintsRelationships;
use Domain\Support\RouteUrl\Contracts\HasRouteUrl as HasRouteUrlContact;
use Domain\Support\RouteUrl\HasRouteUrl;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Domain\Support\MetaData\Contracts\HasMetaData as HasMetaDataContract;
use Domain\Taxonomy\Models\Taxonomy;
use Domain\Taxonomy\Models\TaxonomyTerm;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * Domain\Product\Models\Product
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Product newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Product newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Product query()
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereUpdatedAt($value)
 * @mixin \Eloquent
 */
#[OnDeleteCascade(['metaData'])]
class Product extends Model implements HasMetaDataContract, HasRouteUrlContact, HasMedia
{
    use LogsActivity;
    use HasSlug;
    use HasRouteUrl;
    use HasMetaData;
    use ConstraintsRelationships;
    use InteractsWithMedia;

    protected $fillable = [
        'name',
        'sku',
        'description',
        'retail_price',
        'selling_price',
        'shipping_fee',
        'stock',
        'status',
        'is_digital_product',
        'is_featured',
        'is_special_offer',
        'allow_customer_remarks',
        'allow_remark_with_image'
    ];

    /**
     * Columns that are converted
     * to a specific data type.
     */
    protected $casts = [];

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

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function taxonomyTerms(): BelongsToMany
    {
        return $this->belongsToMany(TaxonomyTerm::class);
    }

    public function taxonomies(): BelongsToMany
    {
        return $this->belongsToMany(Taxonomy::class);
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

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('image')
            ->singleFile();
    }
}
