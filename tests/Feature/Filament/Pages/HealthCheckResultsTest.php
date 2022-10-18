<?php

declare(strict_types=1);

use App\Filament\Pages\HealthCheckResults;

use function Pest\Laravel\get;

beforeEach(fn () => loginAsAdmin());

it('render', function () {
    get(HealthCheckResults::getUrl())
        ->assertOk();
});
