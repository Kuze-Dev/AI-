<?php

declare(strict_types=1);

namespace Domain\Site\Models;

use Domain\Page\Models\Page;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;
use Domain\Collection\Models\Collection;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use AlexJustesen\FilamentSpatieLaravelActivitylog\Contracts\IsActivitySubject;

/**
 * Domain\Site\Models\Site
 *
 * @property string $id
 * @property string $name
 * @property \Domain\Site\DataTransferObjects\SchemaData $schema
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|Activity[] $activities
 * @property-read int|null $activities_count
 * @method static \Illuminate\Database\Eloquent\Builder|Site newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Site newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Site query()
 * @method static \Illuminate\Database\Eloquent\Builder|Site whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Site whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Site whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Site whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Site whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Site extends Model implements IsActivitySubject
{
    use LogsActivity;
    use SoftDeletes;

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
        return 'Site: ' . $this->name;
    }

    public function collections(): BelongsToMany
    {
        return $this->belongsToMany(Collection::class);
    }

    public function pages(): BelongsToMany
    {
        return $this->belongsToMany(Page::class);
    }
}
