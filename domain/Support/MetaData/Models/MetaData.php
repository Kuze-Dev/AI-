<?php

declare(strict_types=1);

namespace Domain\Support\MetaData\Models;

use AlexJustesen\FilamentSpatieLaravelActivitylog\Contracts\IsActivitySubject;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use Eloquent;

/**
 * Domain\Support\MetaData\Models\MetaData
 *
 * @property int $id
 * @property string $model_type
 * @property int $model_id
 * @property string|null $title
 * @property string|null $author
 * @property string|null $description
 * @property string|null $keywords
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|Activity[] $activities
 * @property-read int|null $activities_count
 * @property-read Model|Eloquent $model
 * @method static \Illuminate\Database\Eloquent\Builder|MetaData newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MetaData newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MetaData query()
 * @method static \Illuminate\Database\Eloquent\Builder|MetaData whereAuthor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MetaData whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MetaData whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MetaData whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MetaData whereKeywords($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MetaData whereModelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MetaData whereModelType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MetaData whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MetaData whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class MetaData extends Model implements IsActivitySubject
{
    use LogsActivity;

    /**
     * Declare columns
     * that are mass assignable.
     */
    protected $fillable = [
        'title',
        'author',
        'description',
        'keywords',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /** Specify activity log description. */
    public function getActivitySubjectDescription(Activity $activity): string
    {
        return 'Meta tags: '.$this->title;
    }

    /** @return MorphTo<Model, self> */
    public function model(): MorphTo
    {
        return $this->morphTo();
    }
}
