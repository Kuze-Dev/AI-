<?php

declare(strict_types=1);

namespace Domain\Collection\Models;

use AlexJustesen\FilamentSpatieLaravelActivitylog\Contracts\IsActivitySubject;
use Domain\Taxonomy\Models\TaxonomyTerm;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class CollectionEntry extends Model implements IsActivitySubject
{
    use LogsActivity;
    use HasSlug;

    /**
     * Declare columns
     * that are mass assignable.
     */
    protected $fillable = [
        'title',
        'slug',
        'data',
        'collection_id',
        'taxonomy_term_id',
        'order',
        'published_at'
    ];

    /**
     * Columns that are converted
     * to a specific data type.
     */
    protected $casts = [
        'data' => 'array',
        'published_at' => 'date'
    ];

    /** @return LogOptions */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Declare relationship of
     * current model to collections.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Domain\Collection\Models\Collection, \Domain\Collection\Models\CollectionEntry> 
     */
    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }

    /**
     * Declare relationship of 
     * current model to taxonomy terms.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Domain\Taxonomy\Models\TaxonomyTerm, \Domain\Collection\Models\CollectionEntry>
     */
    public function taxonomyTerm(): BelongsTo
    {
        return $this->belongsTo(TaxonomyTerm::class);
    }

    /** Specify activity log description. */
    public function getActivitySubjectDescription(Activity $activity): string
    {
        return 'Collection Entry: '.$this->id;
    }

    /** @return SlugOptions */
    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->preventOverwrite()
            ->doNotGenerateSlugsOnUpdate()
            ->saveSlugsTo($this->getRouteKeyName());
    }

    /**
     * Set the column reference
     * for route keys.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
