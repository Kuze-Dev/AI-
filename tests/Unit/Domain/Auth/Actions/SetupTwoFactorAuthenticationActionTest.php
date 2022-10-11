<?php

use Domain\Auth\Actions\SetupTwoFactorAuthenticationAction;
use Domain\Auth\Contracts\TwoFactorAuthenticationProvider;
use Mockery\MockInterface;
use function Pest\Laravel\assertDatabaseCount;
use Tests\Fixtures\User;

beforeEach(function () {
    $this->user = User::create(['email' => 'test@user']);
    $this->mock(
        TwoFactorAuthenticationProvider::class,
        fn (MockInterface $mock) => $mock->shouldReceive('generateSecretKey')->andReturn('secret')
    );
});

it('can setup two factor authentication', function () {
    app(SetupTwoFactorAuthenticationAction::class)->execute($this->user);

    assertDatabaseCount('two_factor_authentications', 1);
});
