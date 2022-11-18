<?php

declare(strict_types=1);

namespace Domain\Page\Models;

use AlexJustesen\FilamentSpatieLaravelActivitylog\Contracts\IsActivitySubject;
use Domain\Blueprint\Models\Blueprint;
use Domain\Page\Enums\PageBehavior;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Domain\Page\Models\Page
 *
 * @property int $id
 * @property int $blueprint_id
 * @property string $name
 * @property \Domain\Page\Enums\PageBehavior|null $past_behavior
 * @property \Domain\Page\Enums\PageBehavior|null $future_behavior
 * @property array|null $data
 * @property \Illuminate\Support\Carbon|null $published_at
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

    protected $fillable = [
        'blueprint_id',
        'name',
        'past_behavior',
        'future_behavior',
        'data',
        'published_at',
    ];

    protected $casts = [
        'past_behavior' => PageBehavior::class,
        'future_behavior' => PageBehavior::class,
        'data' => 'array',
        'published_at' => 'datetime',
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

    public function hasPublishedAtBehavior(): bool
    {
        return $this->past_behavior !== null || $this->future_behavior !== null;
    }
}
