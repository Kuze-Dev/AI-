<?php

declare(strict_types=1);

namespace Domain\Blueprint\Models;

use Domain\Blueprint\Models\Casts\SchemaDataCast;
use Domain\Support\ConstraintsRelationships\ConstraintsRelationships;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Domain\Blueprint\Models\Blueprint
 *
 * @property \Domain\Blueprint\DataTransferObjects\SchemaData $schema
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @method static \Illuminate\Database\Eloquent\Builder|Blueprint newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Blueprint newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Blueprint query()
 * @mixin \Eloquent
 */
class Blueprint extends Model
{
    use HasUuids;
    use LogsActivity;
    use ConstraintsRelationships;

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

    protected function onDeleteRestrictRelations(): array
    {
        return array_keys(config('domain.blueprint.relations'));
    }
}
