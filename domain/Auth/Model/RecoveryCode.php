<?php

declare(strict_types=1);

namespace Domain\Auth\Model;

use Illuminate\Database\Eloquent\Model;

/**
 * Domain\Auth\Model\RecoveryCode
 *
 * @property int $id
 * @property int $two_factor_authentication_id
 * @property mixed $code
 * @property int|null $used_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder|RecoveryCode newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RecoveryCode newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RecoveryCode query()
 * @method static \Illuminate\Database\Eloquent\Builder|RecoveryCode whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecoveryCode whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecoveryCode whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecoveryCode whereTwoFactorAuthenticationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecoveryCode whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecoveryCode whereUsedAt($value)
 *
 * @mixin \Eloquent
 */
class RecoveryCode extends Model
{
    protected $fillable = ['code'];

    protected function casts(): array
    {
        return [
            'code' => 'encrypted',
            'used_at' => 'timestamp',
        ];
    }

    public function isUsed(): bool
    {
        return (bool) $this->used_at;
    }

    public function markUsed(): bool
    {
        return $this->forceFill(['used_at' => now()])
            ->save();
    }
}
