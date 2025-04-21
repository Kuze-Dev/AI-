<?php

declare(strict_types=1);

namespace Domain\Auth\Model;

use Illuminate\Database\Eloquent\Model;

/**
 * Domain\Auth\Model\EmailVerificationOneTimePassword
 *
 * @property int $id
 * @property string $authenticatable_type
 * @property int $authenticatable_id
 * @property mixed $password
 * @property \Illuminate\Support\Carbon $expired_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder|EmailVerificationOneTimePassword newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmailVerificationOneTimePassword newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmailVerificationOneTimePassword query()
 * @method static \Illuminate\Database\Eloquent\Builder|EmailVerificationOneTimePassword whereAuthenticatableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailVerificationOneTimePassword whereAuthenticatableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailVerificationOneTimePassword whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailVerificationOneTimePassword whereExpiredAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailVerificationOneTimePassword whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailVerificationOneTimePassword wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailVerificationOneTimePassword whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class EmailVerificationOneTimePassword extends Model
{
    protected $fillable = [
        'password',
        'expired_at',
    ];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'expired_at' => 'datetime',
        ];
    }
}
