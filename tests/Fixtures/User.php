<?php

namespace Tests\Fixtures;

use Domain\Auth\Contracts\TwoFactorAuthenticatable as TwoFactorAuthenticatableContract;
use Domain\Auth\TwoFactorAuthenticatable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as FoundationUser;

class User extends FoundationUser implements TwoFactorAuthenticatableContract, MustVerifyEmail
{
    use TwoFactorAuthenticatable;

    protected $table = 'test_users';

    public $timestamps = false;

    protected $fillable = [
        'id',
        'email',
        'email_verified_at',
    ];
}
