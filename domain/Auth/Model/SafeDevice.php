<?php

declare(strict_types=1);

namespace Domain\Auth\Model;

use Illuminate\Database\Eloquent\Model;

/**
 * Domain\Auth\Model\SafeDevice
 *
 * @property int $id
 * @property int $two_factor_authentication_id
 * @property string $ip
 * @property string $user_agent
 * @property string $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder|SafeDevice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SafeDevice newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SafeDevice query()
 * @method static \Illuminate\Database\Eloquent\Builder|SafeDevice whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SafeDevice whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SafeDevice whereIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SafeDevice whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SafeDevice whereTwoFactorAuthenticationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SafeDevice whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SafeDevice whereUserAgent($value)
 *
 * @mixin \Eloquent
 */
class SafeDevice extends Model
{
    protected $fillable = [
        'ip',
        'user_agent',
    ];
}
