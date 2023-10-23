<?php

declare(strict_types=1);

namespace Domain\Service\Models;

use Domain\Blueprint\Models\Blueprint;
use Domain\Service\Enums\BillingCycleEnum;
use Domain\Taxonomy\Models\TaxonomyTerm;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
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
 * @property bool $auto_generate_bill
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @property-read Blueprint|null $blueprint
 * @property-read MediaCollection<int, Media> $media
 * @property-read int|null $media_count
 * @property-read MetaData|null $metaData
 * @property-read Collection<int, TaxonomyTerm> $taxonomyTerms
 * @property-read int|null $taxonomy_terms_count
 * @method static Builder|Service newModelQuery()
 * @method static Builder|Service newQuery()
 * @method static Builder|Service onlyTrashed()
 * @method static Builder|Service query()
 * @method static Builder|Service whereAutoGenerateBill($value)
 * @method static Builder|Service whereBillingCycle($value)
 * @method static Builder|Service whereBlueprintId($value)
 * @method static Builder|Service whereCreatedAt($value)
 * @method static Builder|Service whereDeletedAt($value)
 * @method static Builder|Service whereDescription($value)
 * @method static Builder|Service whereDueDateEvery($value)
 * @method static Builder|Service whereId($value)
 * @method static Builder|Service whereIsFeatured($value)
 * @method static Builder|Service whereIsSpecialOffer($value)
 * @method static Builder|Service whereIsSubscription($value)
 * @method static Builder|Service whereName($value)
 * @method static Builder|Service whereNeedsApproval($value)
 * @method static Builder|Service wherePayUpfront($value)
 * @method static Builder|Service whereRetailPrice($value)
 * @method static Builder|Service whereSellingPrice($value)
 * @method static Builder|Service whereStatus($value)
 * @method static Builder|Service whereUpdatedAt($value)
 * @method static Builder|Service whereUuid($value)
 * @method static Builder|Service withTrashed()
 * @method static Builder|Service withoutTrashed()
 * @mixin Eloquent
 */
#[OnDeleteCascade(['metaData, taxonomyTerms', 'media'])]
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
        'auto_generate_bill',
    ];

    protected $casts = [
        'billing_cycle' => BillingCycleEnum::class,
        'is_featured' => 'bool',
        'is_special_offer' => 'bool',
        'pay_upfront' => 'bool',
        'is_subscription' => 'bool',
        'status' => 'bool',
        'needs_approval' => 'bool',
        'auto_generate_bill' => 'bool',
    ];

    /** @return BelongsToMany<TaxonomyTerm> */
    public function taxonomyTerms(): BelongsToMany
    {
        return $this->belongsToMany(TaxonomyTerm::class, 'service_taxonomy_terms');
    }

    /** @return BelongsTo<Blueprint, Service> */
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
