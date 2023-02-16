<?php

declare(strict_types=1);

namespace Domain\Globals\Models;

use AlexJustesen\FilamentSpatieLaravelActivitylog\Contracts\IsActivitySubject;
use Domain\Blueprint\Models\Blueprint;
use Domain\Taxonomy\Models\Taxonomy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Domain\Collection\Enums\PublishBehavior;
use Domain\Support\ConstraintsRelationships\Attributes\OnDeleteCascade;
use Domain\Support\ConstraintsRelationships\Attributes\OnDeleteRestrict;
use Domain\Support\ConstraintsRelationships\ConstraintsRelationships;
use Domain\Support\SlugHistory\HasSlugHistory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;


class Globals extends Model implements IsActivitySubject
{
    use LogsActivity;
    use HasSlug;
    use HasSlugHistory;
    use ConstraintsRelationships;

    protected $table = 'globals';
    /**
     * Declare columns
     * that are mass assignable.
     */
    protected $fillable = [
        'name',
        'blueprint_id',
        'slug',
        'data',
        
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
     * current model to blueprint.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Domain\Blueprint\Models\Blueprint, \Domain\Collection\Models\Collection>
     */
    public function blueprint(): BelongsTo
    {
        return $this->belongsTo(Blueprint::class);
    }



 
    /** Specify activity log description. */
    public function getActivitySubjectDescription(Activity $activity): string
    {
        return 'Global: '.$this->name;
    }

    /**
     * Set the column reference
     * for route keys.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /** @return SlugOptions */
    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->preventOverwrite()
            ->doNotGenerateSlugsOnUpdate()
            ->saveSlugsTo($this->getRouteKeyName());
    }

  
}
