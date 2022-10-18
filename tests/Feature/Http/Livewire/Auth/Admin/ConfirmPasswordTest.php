<?php

declare(strict_types=1);

use App\Http\Livewire\Admin\Auth\ConfirmPassword;
use Database\Factories\AdminFactory;

use function Pest\Livewire\livewire;

beforeEach(function () {
    Livewire::actingAs(AdminFactory::new(['email' => 'test@user'])->create());
});

it('can render confirm password', function () {
    livewire(ConfirmPassword::class)->assertSuccessful();
});

it('can confirm password', function () {
    livewire(ConfirmPassword::class)
        ->fill(['password' => 'secret'])
        ->call('confirm')
        ->assertHasNoErrors();
});

it('throws error on invalid password', function () {
    livewire(ConfirmPassword::class)
        ->fill(['password' => 'invalid'])
        ->call('confirm')
        ->assertHasErrors();
});
