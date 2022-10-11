<?php

declare(strict_types=1);

use Domain\Auth\Model\TwoFactorAuthentication;
use Tests\Fixtures\User;

beforeEach(function () {
    $this->twoFactorAuthentication = TwoFactorAuthentication::make()
        ->forceFill(['secret' => 'secret'])
        ->setRelation('authenticatable', User::make(['email' => 'test@user']));
});

it('can generate qr code url', function () {
    expect($this->twoFactorAuthentication->qrCodeUrl())->toBeString();
});

it('can generate qr code svg', function () {
    expect($this->twoFactorAuthentication->qrCodeSvg())->toBeString();
});
