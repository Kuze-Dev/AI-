<?php

declare(strict_types=1);

namespace Domain\Admin\Models;

use Domain\Auth\Contracts\HasActiveState as HasActiveStateContract;
use Domain\Auth\Contracts\TwoFactorAuthenticatable as TwoFactorAuthenticatableContract;
use Domain\Auth\HasActiveState;
use Domain\Auth\TwoFactorAuthenticatable;
use Domain\Site\Models\Site;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\ActivitylogServiceProvider;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\CausesActivity;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Traits\HasRoles;
use Support\ConstraintsRelationships\Attributes\OnDeleteCascade;
use Support\ConstraintsRelationships\ConstraintsRelationships;

/**
 * Domain\Admin\Models\Admin
 *
 * @property int $id
 * @property string $first_name
 * @property string $last_name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property mixed $password
 * @property \Illuminate\Support\Carbon|null $password_changed_at
 * @property bool $active
 * @property string $timezone
 * @property \Illuminate\Support\Carbon|null $last_login_at
 * @property string|null $last_login_ip
 * @property bool $to_be_logged_out
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Domain\Role\Models\Role> $roles
 * @property-read int|null $roles_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @property-read \Domain\Auth\Model\TwoFactorAuthentication|null $twoFactorAuthentication
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Admin newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Admin newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Admin onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Admin permission($permissions)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin query()
 * @method static \Illuminate\Database\Eloquent\Builder|Admin role($roles, $guard = null)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin whereLastLoginAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin whereLastLoginIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin wherePasswordChangedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin whereTimezone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin whereToBeLoggedOut($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Admin withoutTrashed()
 *
 * @mixin \Eloquent
 */
#[OnDeleteCascade(['twoFactorAuthentication', 'roles', 'permissions', 'tokens'])]
class Admin extends Authenticatable implements FilamentUser, HasActiveStateContract, HasName, MustVerifyEmail, TwoFactorAuthenticatableContract
{
    use ConstraintsRelationships;
    use HasActiveState;
    use HasApiTokens;
    use HasRoles;
    use LogsActivity;
    use Notifiable;
    use SoftDeletes;
    use TwoFactorAuthenticatable;
    use CausesActivity;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'timezone',
        'active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'email_verified_at' => 'datetime',
            'password_changed_at' => 'datetime',
            'active' => 'boolean',
            'last_login_at' => 'datetime',
            'to_be_logged_out' => 'boolean',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->logExcept(['password'])
            ->dontSubmitEmptyLogs();
    }

    public function isZeroDayAdmin(): bool
    {
        return $this->id === 1;
    }

    /** @return Attribute<non-falsy-string, never> */
    protected function fullName(): Attribute
    {
        return Attribute::get(
            fn ($value) => "{$this->first_name} {$this->last_name}"
        );
    }

    /** @return Attribute<non-falsy-string, never> */
    protected function siteLabel(): Attribute
    {
        return Attribute::get(
            fn ($value) => "{$this->first_name} {$this->last_name} | {$this->email}"
        );
    }

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<\Domain\Site\Models\Site, $this> */
    public function userSite(): BelongsToMany
    {
        return $this->belongsToMany(Site::class);
    }

    #[\Override]
    public function getFilamentName(): string
    {
        return $this->full_name;
    }

    #[\Override]
    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }
}
