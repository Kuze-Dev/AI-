<?php

declare(strict_types=1);

namespace Domain\Taxonomy\Models;

use AlexJustesen\FilamentSpatieLaravelActivitylog\Contracts\IsActivitySubject;
use Domain\Collection\Models\CollectionEntry;
use Domain\Support\ConstraintsRelationships\Attributes\OnDeleteCascade;
use Domain\Support\ConstraintsRelationships\ConstraintsRelationships;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * Domain\Taxonomy\Models\TaxonomyTerm
 *
 * @property int $id
 * @property int $taxonomy_id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property int $order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|Activity[] $activities
 * @property-read int|null $activities_count
 * @property-read \Illuminate\Database\Eloquent\Collection|CollectionEntry[] $collectionEntries
 * @property-read int|null $collection_entries_count
 * @property-read \Domain\Taxonomy\Models\Taxonomy $taxonomy
 * @method static \Illuminate\Database\Eloquent\Builder|TaxonomyTerm newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TaxonomyTerm newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TaxonomyTerm ordered(string $direction = 'asc')
 * @method static \Illuminate\Database\Eloquent\Builder|TaxonomyTerm query()
 * @method static \Illuminate\Database\Eloquent\Builder|TaxonomyTerm whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaxonomyTerm whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaxonomyTerm whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaxonomyTerm whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaxonomyTerm whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaxonomyTerm whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaxonomyTerm whereTaxonomyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaxonomyTerm whereUpdatedAt($value)
 * @mixin \Eloquent
 */
#[OnDeleteCascade(['collectionEntries'])]
class TaxonomyTerm extends Model implements IsActivitySubject, Sortable
{
    use HasSlug;
    use LogsActivity;
    use SortableTrait;
    use ConstraintsRelationships;

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
        return 'Taxonomy Term: '.$this->name;
    }

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Domain\Taxonomy\Models\Taxonomy, \Domain\Taxonomy\Models\TaxonomyTerm> */
    public function taxonomy(): BelongsTo
    {
        return $this->belongsTo(Taxonomy::class);
    }

    /**
     * Declare relationship of
     * current model to collection entries.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<\Domain\Collection\Models\CollectionEntry>
     */
    public function collectionEntries(): BelongsToMany
    {
        return $this->belongsToMany(CollectionEntry::class);
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
