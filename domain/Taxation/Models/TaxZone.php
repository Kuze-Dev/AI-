<?php

declare(strict_types=1);

namespace Domain\Taxation\Models;

use Domain\Address\Models\Country;
use Domain\Address\Models\State;
use Domain\Taxation\Enums\PriceDisplay;
use Domain\Taxation\Enums\TaxZoneType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Support\ConstraintsRelationships\ConstraintsRelationships;

/**
 * Domain\Taxation\Models\TaxZone
 *
 * @property int $id
 * @property string $name
 * @property PriceDisplay $price_display
 * @property bool $is_active
 * @property bool $is_default
 * @property TaxZoneType $type
 * @property string $percentage
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activities
 * @property-read int|null $activities_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Country> $countries
 * @property-read int|null $countries_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, State> $states
 * @property-read int|null $states_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder|TaxZone newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TaxZone newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TaxZone query()
 * @method static \Illuminate\Database\Eloquent\Builder|TaxZone whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaxZone whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaxZone whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaxZone whereIsDefault($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaxZone whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaxZone wherePercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaxZone wherePriceDisplay($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaxZone whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaxZone whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class TaxZone extends Model
{
    use ConstraintsRelationships;
    use LogsActivity;

    protected $fillable = [
        'name',
        'price_display',
        'is_active',
        'is_default',
        'type',
        'percentage',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'price_display' => PriceDisplay::class,
            'type' => TaxZoneType::class,
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<\Domain\Address\Models\Country, $this> */
    public function countries(): BelongsToMany
    {
        return $this->belongsToMany(Country::class, 'tax_zone_country');
    }

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<\Domain\Address\Models\State, $this> */
    public function states(): BelongsToMany
    {
        return $this->belongsToMany(State::class, 'tax_zone_state');
    }
}
