<?php

declare(strict_types=1);

namespace Domain\Service\Models;

use Domain\Blueprint\Models\Blueprint;
use Domain\Service\Enums\BillingCycle;
use Domain\Taxonomy\Models\TaxonomyTerm;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\InteractsWithMedia;
use Support\ConstraintsRelationships\Attributes\OnDeleteCascade;
use Support\ConstraintsRelationships\ConstraintsRelationships;
use Support\MetaData\HasMetaData;
use Support\MetaData\Contracts\HasMetaData as HasMetaDataContract;
use Spatie\MediaLibrary\HasMedia;

/**
 * @property mixed|null $blueprint
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
    ];

    protected $casts = [
        'billing_cycle' => BillingCycle::class,
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

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
