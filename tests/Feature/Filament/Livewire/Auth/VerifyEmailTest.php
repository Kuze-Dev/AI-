<?php

declare(strict_types=1);

use App\Filament\Livewire\Auth\VerifyEmail;
use App\Filament\Requests\VerifyEmailRequest;
use Domain\Admin\Database\Factories\AdminFactory;
use Mockery\MockInterface;

use function Pest\Livewire\livewire;

it('can verify email', function () {
    $user = AdminFactory::new(['email' => 'test@user'])
        ->unverified()
        ->create();

    $this->mock(
        VerifyEmailRequest::class,
        fn (MockInterface $mock) => $mock->expects('user')->andReturns($user)
    );

    livewire(VerifyEmail::class)->assertNotified();

    expect($user->refresh()->hasVerifiedEmail())->toBeTrue();
});

it('can\'t verify email on invalid user instance', function () {
    $this->partialMock(VerifyEmailRequest::class);

    livewire(VerifyEmail::class)->assertForbidden();
});
