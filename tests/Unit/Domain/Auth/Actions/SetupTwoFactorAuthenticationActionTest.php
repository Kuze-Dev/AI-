<?php

declare(strict_types=1);

use Domain\Auth\Actions\SetupTwoFactorAuthenticationAction;
use Domain\Auth\Contracts\TwoFactorAuthenticationProvider;
use Domain\Auth\Model\TwoFactorAuthentication;
use Mockery\MockInterface;
use Tests\Fixtures\User;

use function Pest\Laravel\assertDatabaseHas;

beforeEach(function () {
    $this->user = User::create(['email' => 'test@user']);
    $this->mock(
        TwoFactorAuthenticationProvider::class,
        fn (MockInterface $mock) => $mock->expects('generateSecretKey')->andReturns('secret')
    );
})->skip('skip otp');

it('can setup two factor authentication', function () {
    app(SetupTwoFactorAuthenticationAction::class)->execute($this->user);

    assertDatabaseHas(TwoFactorAuthentication::class, [
        'authenticatable_id' => $this->user->getKey(),
        'authenticatable_type' => $this->user->getMorphClass(),
    ]);
});
