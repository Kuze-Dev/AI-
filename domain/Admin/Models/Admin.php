<?php

declare(strict_types=1);

namespace Domain\Admin\Models;

use Domain\Admin\Notifications\ResetPassword;
use Domain\Admin\Notifications\VerifyEmail;
use Domain\Auth\Contracts\HasActiveState as HasActiveStateContract;
use Domain\Auth\Contracts\TwoFactorAuthenticatable as TwoFactorAuthenticatableContract;
use Domain\Auth\HasActiveState;
use Domain\Auth\TwoFactorAuthenticatable;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

/**
 * Domain\Admin\Models\Admin
 *
 * @property int $id
 * @property string $first_name
 * @property string $last_name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
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
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\Spatie\Permission\Models\Permission[] $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\Spatie\Permission\Models\Role[] $roles
 * @property-read int|null $roles_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\Laravel\Sanctum\PersonalAccessToken[] $tokens
 * @property-read int|null $tokens_count
 * @method static \Illuminate\Database\Eloquent\Builder|Admin newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Admin newQuery()
 * @method static \Illuminate\Database\Query\Builder|Admin onlyTrashed()
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
 * @method static \Illuminate\Database\Query\Builder|Admin withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Admin withoutTrashed()
 * @mixin \Eloquent
 */
class Admin extends Authenticatable implements MustVerifyEmail, HasName, TwoFactorAuthenticatableContract, FilamentUser, HasActiveStateContract
{
    use HasApiTokens;
    use HasRoles;
    use Notifiable;
    use SoftDeletes;
    use TwoFactorAuthenticatable;
    use HasActiveState;

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

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password_changed_at' => 'datetime',
        'active' => 'boolean',
        'last_login_at' => 'datetime',
        'to_be_logged_out' => 'boolean',
    ];

    public function isSuperAdmin(): bool
    {
        return $this->id === 1;
    }

    /** @return Attribute<string, never> */
    protected function fullName(): Attribute
    {
        return Attribute::get(
            fn ($value) => "{$this->first_name} {$this->last_name}"
        );
    }

    /** @return Attribute<never, string> */
    protected function password(): Attribute
    {
        return Attribute::set(
            fn ($value) => Hash::info($value)['algoName'] === 'unknown'
                ? Hash::make($value)
                : $value
        );
    }

    public function getFilamentName(): string
    {
        return $this->full_name;
    }

    public function canAccessFilament(): bool
    {
        return true;
    }

    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new VerifyEmail());
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPassword($token));
    }
}
