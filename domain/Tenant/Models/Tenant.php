<?php

declare(strict_types=1);

namespace Domain\Tenant\Models;

use AlexJustesen\FilamentSpatieLaravelActivitylog\Contracts\IsActivitySubject;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Stancl\Tenancy\Database\Models\Domain;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;

/**
 * @property-read Domain[]|\Illuminate\Database\Eloquent\Collection $domains
 */
class Tenant extends BaseTenant implements TenantWithDatabase, IsActivitySubject
{
    use HasDatabase;
    use HasDomains;
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function getActivitySubjectDescription(Activity $activity): string
    {
        return 'Tenant: '.$this->name;
    }

    protected $fillable = [
        'name',
    ];

    public static function getCustomColumns(): array
    {
        return [
            'id',
            'name',
        ];
    }
}
