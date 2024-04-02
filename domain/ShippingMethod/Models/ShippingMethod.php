<?php

declare(strict_types=1);

namespace Domain\ShippingMethod\Models;

use Domain\Address\Models\Country;
use Domain\Address\Models\State;
use Domain\Shipment\Models\Shipment;
use Domain\ShippingMethod\Enums\Driver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Support\ConstraintsRelationships\ConstraintsRelationships;

/**
 * Domain\ShippingMethod\Models\ShippingMethod
 *
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property string|null $subtitle
 * @property string|null $description
 * @property int $shipper_country_id
 * @property int $shipper_state_id
 * @property string $shipper_address
 * @property string $shipper_city
 * @property string $shipper_zipcode
 * @property Driver $driver
 * @property bool $active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activities
 * @property-read int|null $activities_count
 * @property-read Country $country
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \Spatie\MediaLibrary\MediaCollections\Models\Media> $media
 * @property-read int|null $media_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Shipment> $shipments
 * @property-read int|null $shipments_count
 * @property-read State $state
 *
 * @method static \Illuminate\Database\Eloquent\Builder|ShippingMethod newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ShippingMethod newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ShippingMethod query()
 * @method static \Illuminate\Database\Eloquent\Builder|ShippingMethod whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShippingMethod whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShippingMethod whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShippingMethod whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShippingMethod whereDriver($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShippingMethod whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShippingMethod whereShipperAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShippingMethod whereShipperCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShippingMethod whereShipperCountryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShippingMethod whereShipperStateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShippingMethod whereShipperZipcode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShippingMethod whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShippingMethod whereSubtitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShippingMethod whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShippingMethod whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class ShippingMethod extends Model implements HasMedia
{
    use ConstraintsRelationships;
    use HasSlug;
    use InteractsWithMedia;
    use LogsActivity;

    protected $fillable = [
        'title',
        'slug',
        'subtitle',
        'description',
        'driver',
        'shipper_country_id',
        'shipper_state_id',
        'shipper_address',
        'shipper_city',
        'shipper_zipcode',
        'active',
    ];

    protected $casts = [
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

    #[\Override]
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

    #[\Override]
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('logo')
            ->singleFile();
    }

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Domain\Address\Models\Country, \Domain\ShippingMethod\Models\ShippingMethod> */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'shipper_country_id');
    }

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Domain\Address\Models\State, \Domain\ShippingMethod\Models\ShippingMethod> */
    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class, 'shipper_state_id');
    }
}
