<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use Domain\Auth\Contracts\HasActiveState as HasActiveStateContract;
use Domain\Auth\Contracts\TwoFactorAuthenticatable as TwoFactorAuthenticatableContract;
use Domain\Auth\HasActiveState;
use Domain\Auth\TwoFactorAuthenticatable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as FoundationUser;
use Illuminate\Notifications\Notifiable;

class User extends FoundationUser implements TwoFactorAuthenticatableContract, MustVerifyEmail, HasActiveStateContract
{
    use TwoFactorAuthenticatable;
    use HasActiveState;
    use Notifiable;

    protected $table = 'test_users';

    public $timestamps = false;

    protected $fillable = [
        'id',
        'email',
        'email_verified_at',
        'active',
    ];
}
