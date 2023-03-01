<?php

declare(strict_types=1);

use Domain\Site\Database\Factories\SiteFactory;
use Illuminate\Testing\Fluent\AssertableJson;

use function Pest\Laravel\getJson;

beforeEach(function () {
    testInTenantContext();
});

it('can list sites', function () {
    SiteFactory::new()
        ->count(2)
        ->create();

    getJson('api/sites')
        ->assertOk()
        ->assertJson(function (AssertableJson $json) {
            $json
                ->count('data', 2)
                ->whereType('data.0.attributes.name', 'string')
                ->etc();
        });
});
