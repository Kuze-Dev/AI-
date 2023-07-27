<?php

declare(strict_types=1);

namespace Domain\Auth\Model;

use Illuminate\Database\Eloquent\Model;

class EmailVerificationOneTimePassword extends Model
{
    protected $fillable = [
        'password',
        'expired_at',
    ];

    protected $casts = [
        'password' => 'hashed',
        'expired_at' => 'datetime',
    ];

    protected $hidden = [
        'password',
    ];
}
