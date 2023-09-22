<?php

declare(strict_types=1);

namespace Domain\Tier\Models;

use Domain\Customer\Models\Customer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Domain\Customer\Models\Tier
 *
 * @property int $id
 * @property string $name
 * @property string $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activities
 * @property-read int|null $activities_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Customer> $customers
 * @property-read int|null $customers_count
 * @method static \Illuminate\Database\Eloquent\Builder|Tier newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Tier newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Tier onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Tier query()
 * @method static \Illuminate\Database\Eloquent\Builder|Tier whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tier whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tier whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tier whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tier whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tier whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tier withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Tier withoutTrashed()
 * @mixin \Eloquent
 */
class Tier extends Model
{
    use SoftDeletes;
    use LogsActivity;

    protected $fillable = [
        'name',
        'description',
        'has_approval',
    ];

    protected $casts = [
        'has_approval' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /** @return \Illuminate\Database\Eloquent\Relations\HasMany<\Domain\Customer\Models\Customer> */
    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }
}
