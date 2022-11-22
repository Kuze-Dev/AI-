<?php

declare(strict_types=1);

namespace Domain\Page\Models;

use AlexJustesen\FilamentSpatieLaravelActivitylog\Contracts\IsActivitySubject;
use Domain\Blueprint\Models\Blueprint;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * Domain\Page\Models\Page
 *
 * @property int $id
 * @property int $blueprint_id
 * @property string $name
 * @property array|null $data
 * @property string $slug
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\Spatie\Activitylog\Models\Activity[] $activities
 * @property-read int|null $activities_count
 * @property-read \Domain\Blueprint\Models\Blueprint $blueprint
 * @method static \Illuminate\Database\Eloquent\Builder|\Domain\Page\Models\Page newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Domain\Page\Models\Page newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Domain\Page\Models\Page query()
 * @mixin \Eloquent
 */
class Page extends Model implements IsActivitySubject
{
    use LogsActivity;
    use HasSlug;

    protected $fillable = [
        'blueprint_id',
        'name',
        'slug',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Domain\Blueprint\Models\Blueprint, \Domain\Page\Models\Page> */
    public function blueprint(): BelongsTo
    {
        return $this->belongsTo(Blueprint::class);
    }

    public function getActivitySubjectDescription(Activity $activity): string
    {
        return 'Page: '.$this->name;
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
