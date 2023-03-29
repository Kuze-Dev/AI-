<?php

declare(strict_types=1);

use function Pest\Laravel\getJson;

beforeEach(function () {
    testInTenantContext();
});

it('can get settings', function () {
    getJson('api/route/aaaa')
        ->assertOk();
});
