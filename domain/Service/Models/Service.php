<?php

declare(strict_types=1);

namespace Domain\Service\Models;

use Domain\Blueprint\Models\Blueprint;
use Domain\Service\Enums\BillingCycleEnum;
use Domain\Taxonomy\Models\TaxonomyTerm;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Support\ConstraintsRelationships\Attributes\OnDeleteCascade;
use Support\ConstraintsRelationships\ConstraintsRelationships;
use Support\MetaData\HasMetaData;
use Support\MetaData\Contracts\HasMetaData as HasMetaDataContract;
use Spatie\MediaLibrary\HasMedia;
use Support\MetaData\Models\MetaData;

/**
 * Domain\Service\Models\Service
 *
 * @property int $id
 * @property string $uuid
 * @property string $blueprint_id
 * @property string $name
 * @property string|null $description
 * @property float $retail_price
 * @property float $selling_price
 * @property BillingCycleEnum|null $billing_cycle
 * @property int|null $due_date_every
 * @property int $is_featured
 * @property int $is_special_offer
 * @property int $pay_upfront
 * @property int $is_subscription
 * @property int $status
 * @property int $needs_approval
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activities
 * @property-read int|null $activities_count
 * @property-read Blueprint|null $blueprint
 * @property-read MediaCollection<int, Media> $media
 * @property-read int|null $media_count
 * @property-read MetaData|null $metaData
 * @property-read \Illuminate\Database\Eloquent\Collection<int, TaxonomyTerm> $taxonomyTerms
 * @property-read int|null $taxonomy_terms_count
 * @method static \Illuminate\Database\Eloquent\Builder|Service newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Service newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Service onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Service query()
 * @method static \Illuminate\Database\Eloquent\Builder|Service whereBillingCycle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service whereBlueprintId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service whereDueDateEvery($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service whereIsFeatured($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service whereIsSpecialOffer($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service whereIsSubscription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service wherePayUpfront($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service whereRetailPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service whereSellingPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Service withoutTrashed()
 * @mixin \Eloquent
 */
#[OnDeleteCascade(['metaData, taxonomyTerms'])]
class Service extends Model implements HasMetaDataContract, HasMedia
{
    use LogsActivity;
    use HasMetaData;
    use InteractsWithMedia;
    use ConstraintsRelationships;
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'blueprint_id',
        'name',
        'description',
        'retail_price',
        'selling_price',
        'billing_cycle',
        'due_date_every',
        'is_featured',
        'is_special_offer',
        'pay_upfront',
        'is_subscription',
        'status',
        'needs_approval',
    ];

    protected $casts = [
        'billing_cycle' => BillingCycleEnum::class,
    ];

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<\Domain\Taxonomy\Models\TaxonomyTerm> */
    public function taxonomyTerms(): BelongsToMany
    {
        return $this->belongsToMany(TaxonomyTerm::class, 'service_taxonomy_terms');
    }

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Domain\Blueprint\Models\Blueprint, \Domain\Service\Models\Service> */
    public function blueprint(): BelongsTo
    {
        return $this->belongsTo(Blueprint::class);
    }

    public function defaultMetaData(): array
    {
        return [
            'title' => $this->name,
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
