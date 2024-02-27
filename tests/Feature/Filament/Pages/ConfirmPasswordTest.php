<?php

declare(strict_types=1);

use App\Filament\Pages\ConfirmPassword;
use Domain\Admin\Database\Factories\AdminFactory;

use function Pest\Livewire\livewire;

beforeEach(function () {
    Livewire::actingAs(AdminFactory::new(['email' => 'test@user'])->create());
});

it('can render confirm password', function () {
    livewire(ConfirmPassword::class)->assertSuccessful();
});

it('can confirm password', function () {
    livewire(ConfirmPassword::class)
        ->fillForm(['password' => 'secret'])
        ->call('confirm')
        ->assertHasNoErrors();
});

it('throws error on invalid password', function () {
    livewire(ConfirmPassword::class)
        ->fillForm(['password' => 'invalid'])
        ->call('confirm')
        ->assertHasErrors();
});
