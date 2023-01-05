<?php

declare(strict_types=1);

namespace Domain\Taxonomy\Models;

use AlexJustesen\FilamentSpatieLaravelActivitylog\Contracts\IsActivitySubject;
use Domain\Collection\Models\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\Models\Activity;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Taxonomy extends Model implements IsActivitySubject
{
    use HasSlug;
    use LogsActivity;

    protected $fillable = [
        'name',
        'slug',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function getActivitySubjectDescription(Activity $activity): string
    {
        return 'Taxonomy: '.$this->name;
    }

    /** @return \Illuminate\Database\Eloquent\Relations\HasMany<\Domain\Taxonomy\Models\TaxonomyTerm> */
    public function taxonomyTerms(): HasMany
    {
        return $this->hasMany(TaxonomyTerm::class);
    }

    /**
     * Declare relationship of
     * current model to collections.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\Domain\Collection\Models\Collection>
     */
    public function collections(): HasMany
    {
        return $this->hasMany(Collection::class);
    }

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
