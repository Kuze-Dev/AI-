<?php

use Domain\Auth\Actions\ConfirmPasswordAction;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

it('can confirm password', function () {
    Session::shouldReceive('isStarted')
        ->once()
        ->andReturn(true);
    Session::shouldReceive('put')
        ->once()
        ->andReturn();
    Validator::shouldReceive('validate')
        ->once()
        ->andReturn(true);

    $result = app(ConfirmPasswordAction::class)->execute('secret');

    expect($result)->toBeTrue();
});

it('can\'t confirm password when session is not started', function () {
    Session::shouldReceive('isStarted')
        ->once()
        ->andReturn(false);

    $result = app(ConfirmPasswordAction::class)->execute('secret');

    expect($result)->toBeFalse();
});

it('throws on invalid password', function () {
    Session::shouldReceive('isStarted')
        ->once()
        ->andReturn(true);

    $validationException = ValidationException::withMessages([
        'password' => 'invalid',
    ]);

    Validator::shouldReceive('validate')
        ->once()
        ->andThrow($validationException);

    $result = app(ConfirmPasswordAction::class)->execute('secret');

    expect($result)->toBeTrue();
})->throws(ValidationException::class);
