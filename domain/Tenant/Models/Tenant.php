<?php

declare(strict_types=1);

namespace Domain\Tenant\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Request as RequestFacade;
use Laravel\Pennant\Concerns\HasFeatures;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Stancl\Tenancy\Database\Models\Domain;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Support\ConstraintsRelationships\Attributes\OnDeleteCascade;
use Support\ConstraintsRelationships\ConstraintsRelationships;

/**
 * Domain\Tenant\Models\Tenant
 *
 * @property string $id
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property array|null $data
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Domain> $domains
 * @property-read int|null $domains_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, TenantApiCall> $apiCalls
 *
 * @method static \Stancl\Tenancy\Database\TenantCollection<int, static> all($columns = ['*'])
 * @method static \Stancl\Tenancy\Database\TenantCollection<int, static> get($columns = ['*'])
 * @method static \Illuminate\Database\Eloquent\Builder|Tenant newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Tenant newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Tenant query()
 * @method static \Illuminate\Database\Eloquent\Builder|Tenant whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tenant whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tenant whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tenant whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tenant whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[OnDeleteCascade(['domains'])]
class Tenant extends BaseTenant implements TenantWithDatabase
{
    use ConstraintsRelationships;
    use HasDatabase;
    use HasDomains;
    use HasFeatures;
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    protected function casts(): array
    {
        return [
            'is_suspended' => 'boolean',
        ];
    }

    #[\Override]
    public static function getCustomColumns(): array
    {
        return [
            'id',
            'name',
        ];
    }

    public function getTotalApiRequestAttribute(): string
    {
        return (string) $this->apiCalls()->sum('count');
    }

    /** @return HasMany<TenantApiCall, $this> */
    public function apiCalls(): HasMany
    {
        return $this->hasmany(TenantApiCall::class);
    }

    public function domainFirstUrl(): string
    {
        return RequestFacade::getScheme().'://'.$this->domains[0]?->domain;
    }

    public function syncFeature(array $features): void
    {
        $feature = $this->features();
        $feature->deactivate(collect($feature->all())->keys());
        $feature->activate($features);
    }
}
