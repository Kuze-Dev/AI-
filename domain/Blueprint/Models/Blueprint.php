<?php

declare(strict_types=1);

namespace Domain\Blueprint\Models;

use Domain\Blueprint\Models\Casts\SchemaDataCast;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use Support\ConstraintsRelationships\ConstraintsRelationships;

/**
 * Domain\Blueprint\Models\Blueprint
 *
 * @property string $id
 * @property string $name
 * @property \Domain\Blueprint\DataTransferObjects\SchemaData $schema
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Blueprint newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Blueprint newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Blueprint query()
 * @method static \Illuminate\Database\Eloquent\Builder|Blueprint whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Blueprint whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Blueprint whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Blueprint whereSchema($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Blueprint whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Blueprint extends Model
{
    use ConstraintsRelationships;
    use HasUuids;
    use LogsActivity;

    protected $fillable = [
        'name',
        'schema',
    ];

    protected function casts(): array
    {
        return [
            'schema' => SchemaDataCast::class,
        ];
    }

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
