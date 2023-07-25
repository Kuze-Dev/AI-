<?php

declare(strict_types=1);

namespace Domain\ShippingMethod\Models;

use Domain\Shipment\Models\Shipment;
use Domain\ShippingMethod\Enums\Driver;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Support\ConstraintsRelationships\ConstraintsRelationships;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * Domain\ShippingMethod\Models\ShippingMethod
 *
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property string|null $subtitle
 * @property string|null $description
 * @property Driver $driver
 * @property array $ship_from_address
 * @property bool $active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activities
 * @property-read int|null $activities_count
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \Spatie\MediaLibrary\MediaCollections\Models\Media> $media
 * @property-read int|null $media_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Shipment> $shipments
 * @property-read int|null $shipments_count
 * @method static \Illuminate\Database\Eloquent\Builder|ShippingMethod newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ShippingMethod newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ShippingMethod query()
 * @method static \Illuminate\Database\Eloquent\Builder|ShippingMethod whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShippingMethod whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShippingMethod whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShippingMethod whereDriver($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShippingMethod whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShippingMethod whereShipFromAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShippingMethod whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShippingMethod whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShippingMethod whereSubtitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShippingMethod whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShippingMethod whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ShippingMethod extends Model implements HasMedia
{
    use LogsActivity;
    use HasSlug;
    use ConstraintsRelationships;
    use InteractsWithMedia;

    protected $fillable = [
        'title',
        'slug',
        'subtitle',
        'description',
        'driver',
        'ship_from_address',
        'active',
    ];

    protected $casts = [
        'ship_from_address' => 'array',
        'active' => 'bool',
        'driver' => Driver::class,
    ];

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

    /** @return HasMany<\Domain\Shipment\Models\Shipment> */
    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class);
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->preventOverwrite()
            ->doNotGenerateSlugsOnUpdate()
            ->saveSlugsTo($this->getRouteKeyName());
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('logo')
            ->singleFile();
    }
}
