<?php

declare(strict_types=1);

namespace Domain\Taxonomy\Models;

use AlexJustesen\FilamentSpatieLaravelActivitylog\Contracts\IsActivitySubject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class TaxonomyTerm extends Model implements IsActivitySubject
{
    use LogsActivity;
    use HasFactory;
    use HasSlug;

    protected $fillable = [
        'taxonomy_id',
        'name',
        'slug',
        'description',
        'order',
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
        return 'TaxonomyTerm: '.$this->name;
    }

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Domain\Taxonomy\Models\Taxonomy, \Domain\Taxonomy\Models\TaxonomyTerm> */
    public function taxonomies(): BelongsTo
    {
        return $this->belongsTo(Taxonomy::class);
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
