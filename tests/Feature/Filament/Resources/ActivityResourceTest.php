<?php

declare(strict_types=1);

use App\Filament\Resources\ActivityResource;

use function Pest\Laravel\get;

beforeEach(fn () => loginAsSuperAdmin());

it('render', function () {
    get(ActivityResource::getUrl())
        ->assertOk();
});
