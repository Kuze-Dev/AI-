<?php

declare(strict_types=1);

namespace Domain\Service\Models;

use Domain\Blueprint\Models\Blueprint;
use Domain\Taxonomy\Models\TaxonomyTerm;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\MediaLibrary\InteractsWithMedia;
use Support\MetaData\HasMetaData;
use Support\MetaData\Contracts\HasMetaData as HasMetaDataContract;
use Spatie\MediaLibrary\HasMedia;

/**
 * @property mixed|null $blueprint
 */
class Service extends Model implements HasMetaDataContract, HasMedia
{
    use HasMetaData;
    use InteractsWithMedia;

    protected $fillable = [
        'blueprint_id',
        'name',
        //        'slug',
        'description',
        'price',
        'data',
        'is_featured',
        'is_special_offer',
        'is_subscription',
        'status',
    ];

    protected $casts = [
        'data' => 'json',
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
}
