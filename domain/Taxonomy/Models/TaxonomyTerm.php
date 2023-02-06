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
use Illuminate\Database\Eloquent\Relations\HasMany;

use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Illuminate\Database\Eloquent\Builder;

/**
 * Domain\Taxonomy\Models\TaxonomyTerm
 *
 * @property int $id
 * @property string $taxonomy_id
 * @property int $name
 * @property int|null $parent_id
 * @property string|null $slug
 * @property string|null $description
 * @property int $order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|TaxonomyTerm[] $children
 * @property-read int|null $children_count
 * @property-read \Domain\Taxonomy\Models\Taxonomy|null $taxonomy
 * @method static Builder|TaxonomyTerm newModelQuery()
 * @method static Builder|TaxonomyTerm newQuery()
 * @method static Builder|TaxonomyTerm ordered(string $direction = 'asc')
 * @method static Builder|TaxonomyTerm query()
 * @method static Builder|TaxonomyTerm whereCreatedAt($value)
 * @method static Builder|TaxonomyTerm whereId($value)
 * @method static Builder|TaxonomyTerm whereTaxonomyId($value)
 * @method static Builder|TaxonomyTerm whereOrder($value)
 * @method static Builder|TaxonomyTerm whereParentId($value)
 * @method static Builder|TaxonomyTerm whereName($value)
 * @method static Builder|TaxonomyTerm whereSlug($value)
 * @method static Builder|TaxonomyTerm whereDescription($value)
 * @method static Builder|TaxonomyTerm whereUpdatedAt($value)
 * @mixin \Eloquent
 */
#[OnDeleteCascade(['collectionEntries', 'children'])]
class TaxonomyTerm extends Model implements IsActivitySubject, Sortable
{
    use HasSlug;
    use LogsActivity;
    use SortableTrait;
    use ConstraintsRelationships;

    protected $fillable = [
        'taxonomy_id',
        'parent_id',
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

    /** @return HasMany<TaxonomyTerm> */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->ordered()->with('children');
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

    /** @return Builder<TaxonomyTerm> */
    public function buildSortQuery(): Builder
    {
        return static::query()->whereTaxonomyId($this->taxonomy_id)->whereParentId($this->parent_id);
    }
}
