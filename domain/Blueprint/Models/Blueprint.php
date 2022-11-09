<?php

declare(strict_types=1);

namespace Domain\Blueprint\Models;

use AlexJustesen\FilamentSpatieLaravelActivitylog\Contracts\IsActivitySubject;
use Domain\Blueprint\Models\Casts\SchemaDataCast;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Domain\Blueprint\Models\Blueprint
 *
 * @property \Domain\Blueprint\DataTransferObjects\SchemaData $schema
 * @method static \Illuminate\Database\Eloquent\Builder|Blueprint newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Blueprint newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Blueprint query()
 * @mixin \Eloquent
 */
class Blueprint extends Model implements IsActivitySubject
{
    use LogsActivity;

    protected $fillable = [
        'name',
        'schema',
    ];

    protected $casts = [
        'schema' => SchemaDataCast::class,
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
        return 'Blueprint: '.$this->name;
    }
}
