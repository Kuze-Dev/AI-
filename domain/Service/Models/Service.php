<?php

namespace Domain\Service\Models;

use Domain\Taxonomy\Models\TaxonomyTerm;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Support\ConstraintsRelationships\ConstraintsRelationships;
use Support\MetaData\HasMetaData;

class Service extends Model
{
    use HasMetaData;
    use InteractsWithMedia;

    protected $fillable = [
        'blueprint_id',
        'name',
        'slug',
        'description',
        'price',
        'is_featured',
        'is_special_offer',
        'is_subscription',
        'status',
    ];

    public function taxonomyTerms(): BelongsToMany
    {
        return $this->belongsToMany(TaxonomyTerm::class);
    }

}
