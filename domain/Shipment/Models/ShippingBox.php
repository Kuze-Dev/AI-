<?php

declare(strict_types=1);

namespace Domain\Shipment\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * Domain\Shipment\Models\ShippingBox
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $package_type
 * @property string $courier
 * @property string $dimension_units
 * @property float $length
 * @property float $width
 * @property float $height
 * @property float $volume
 * @property string|null $weight_units
 * @property float $max_weight
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activities
 * @property-read int|null $activities_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder|ShippingBox newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ShippingBox newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ShippingBox query()
 * @method static \Illuminate\Database\Eloquent\Builder|ShippingBox whereCourier($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShippingBox whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShippingBox whereDimensionUnits($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShippingBox whereHeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShippingBox whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShippingBox whereLength($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShippingBox whereMaxWeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShippingBox whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShippingBox wherePackageType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShippingBox whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShippingBox whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShippingBox whereVolume($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShippingBox whereWeightUnits($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShippingBox whereWidth($value)
 *
 * @mixin \Eloquent
 */
class ShippingBox extends Model
{
    use HasSlug;
    use LogsActivity;

    protected $fillable = [
        'name',
        'courier',
        'dimension_units',
        'weight_units',
        'length',
        'width',
        'height',
        'volume',
        'max_weight',
        'package_type',
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

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->preventOverwrite()
            ->doNotGenerateSlugsOnUpdate()
            ->saveSlugsTo($this->getRouteKeyName());
    }
}
