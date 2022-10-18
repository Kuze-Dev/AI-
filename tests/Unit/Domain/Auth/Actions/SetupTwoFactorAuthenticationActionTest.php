<?php

declare(strict_types=1);

use Domain\Auth\Actions\SetupTwoFactorAuthenticationAction;
use Domain\Auth\Contracts\TwoFactorAuthenticationProvider;
use Mockery\MockInterface;
use Tests\Fixtures\User;

use function Pest\Laravel\assertDatabaseCount;

beforeEach(function () {
    $this->user = User::create(['email' => 'test@user']);
    $this->mock(
        TwoFactorAuthenticationProvider::class,
        fn (MockInterface $mock) => $mock->expects('generateSecretKey')->andReturns('secret')
    );
});

it('can setup two factor authentication', function () {
    app(SetupTwoFactorAuthenticationAction::class)->execute($this->user);

    assertDatabaseCount('two_factor_authentications', 1);
});
