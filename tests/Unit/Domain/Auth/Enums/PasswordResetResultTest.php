<?php

declare(strict_types=1);

use Domain\Auth\Enums\PasswordResetResult;
use Illuminate\Validation\ValidationException;

it('can get translated message', function (PasswordResetResult $passwordResetResult) {
    expect($passwordResetResult->getMessage())
        ->toBeString()
        ->not()->toEqual($passwordResetResult->value);
})->with([
    'RESET_LINK_SENT' => PasswordResetResult::RESET_LINK_SENT,
    'PASSWORD_RESET' => PasswordResetResult::PASSWORD_RESET,
    'INVALID_USER' => PasswordResetResult::INVALID_USER,
    'INVALID_TOKEN' => PasswordResetResult::INVALID_TOKEN,
    'RESET_THROTTLED' => PasswordResetResult::RESET_THROTTLED,
]);

it('is failed', function (PasswordResetResult $passwordResetResult) {
    expect($passwordResetResult->failed())
        ->toBeTrue();
})->with([
    'INVALID_USER' => PasswordResetResult::INVALID_USER,
    'INVALID_TOKEN' => PasswordResetResult::INVALID_TOKEN,
    'RESET_THROTTLED' => PasswordResetResult::RESET_THROTTLED,
]);

it('throws exception when failed', function (PasswordResetResult $passwordResetResult) {
    $passwordResetResult->throw();
})->with([
    'INVALID_USER' => PasswordResetResult::INVALID_USER,
    'INVALID_TOKEN' => PasswordResetResult::INVALID_TOKEN,
    'RESET_THROTTLED' => PasswordResetResult::RESET_THROTTLED,
])->throws(ValidationException::class);
