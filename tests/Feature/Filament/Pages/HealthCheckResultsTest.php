<?php

declare(strict_types=1);

use App\Filament\Pages\HealthCheckResults;

use function Pest\Livewire\livewire;

beforeEach(fn () => loginAsAdmin());

it('render', function () {
    livewire(HealthCheckResults::class)
        ->assertSuccessful();
});
