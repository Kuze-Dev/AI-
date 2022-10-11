<?php

use Domain\Auth\Contracts\TwoFactorAuthenticationProvider;

it('can generate secret key', function () {
    $secret = app(TwoFactorAuthenticationProvider::class)->generateSecretKey();

    expect($secret)->toBeString();
});

it('can generate qr code', function () {
    $secret = app(TwoFactorAuthenticationProvider::class)->generateSecretKey();

    $qrCodeUrl = app(TwoFactorAuthenticationProvider::class)->qrCodeUrl(config('app.name'), 'test@user', $secret);

    expect($qrCodeUrl)->toBeString();
});

it('can verify code', function () {
    $secret = app(TwoFactorAuthenticationProvider::class)->generateSecretKey();

    $code = app(\PragmaRX\Google2FA\Google2FA::class)->getCurrentOtp($secret);

    $result = app(TwoFactorAuthenticationProvider::class)->verify($secret, $code);

    expect($result)->toBeTrue();
});

it('can\'t verify used code', function () {
    $secret = app(TwoFactorAuthenticationProvider::class)->generateSecretKey();

    $code = app(\PragmaRX\Google2FA\Google2FA::class)->getCurrentOtp($secret);

    app(TwoFactorAuthenticationProvider::class)->verify($secret, $code);

    $result = app(TwoFactorAuthenticationProvider::class)->verify($secret, $code);

    expect($result)->toBeFalse();
});

it('can\'t verify invalid code', function () {
    $secret = app(TwoFactorAuthenticationProvider::class)->generateSecretKey();

    $result = app(TwoFactorAuthenticationProvider::class)->verify($secret, 'invalid');

    expect($result)->toBeFalse();
});
