<?php

declare(strict_types=1);

use App\Http\Livewire\Admin\Auth\VerifyEmail;
use App\Http\Requests\Admin\Auth\VerifyEmailRequest;
use Database\Factories\AdminFactory;
use Illuminate\Foundation\Auth\User;
use Mockery\MockInterface;

use function Pest\Livewire\livewire;

it('can verify email', function () {
    $user = AdminFactory::new(['email' => 'test@user'])
        ->unverified()
        ->create();

    $this->mock(
        VerifyEmailRequest::class,
        fn (MockInterface $mock) => $mock->allows(['user' => $user])
    );

    livewire(VerifyEmail::class)->assertNotified();

    expect($user->refresh()->hasVerifiedEmail())->toBeTrue();
});

// it('can\'t verify email on invalid user instance', function () {
//     $this->partialMock(VerifyEmailRequest::class);

//     livewire(VerifyEmail::class)->assertUnauthorized();
// })->only();
// TODO: ask Lloric for help
