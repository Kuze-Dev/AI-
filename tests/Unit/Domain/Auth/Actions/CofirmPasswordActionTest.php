<?php

declare(strict_types=1);

use Domain\Auth\Actions\ConfirmPasswordAction;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
beforeEach()->skip('skip otp');

it('can confirm password', function () {
    Session::shouldReceive('put')
        ->once()
        ->andReturn();
    Validator::shouldReceive('validate')
        ->once()
        ->andReturn(true);

    $result = app(ConfirmPasswordAction::class)->execute('secret');

    expect($result)->toBeTrue();
});

it('throws on invalid password', function () {
    $validationException = ValidationException::withMessages([
        'password' => 'invalid',
    ]);

    Validator::shouldReceive('validate')
        ->once()
        ->andThrow($validationException);

    $result = app(ConfirmPasswordAction::class)->execute('secret');

    expect($result)->toBeTrue();
})->throws(ValidationException::class);
