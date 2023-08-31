<?php

declare(strict_types=1);

use Domain\Globals\Database\Factories\GlobalsFactory;
use Domain\Site\Database\Factories\SiteFactory;
use Illuminate\Testing\Fluent\AssertableJson;

use function Pest\Laravel\getJson;

beforeEach(function () {
    testInTenantContext();
});

it('can list globals', function () {
    GlobalsFactory::new()
        ->withDummyBlueprint()
        ->count(10)
        ->create();

    getJson('api/globals')
        ->assertOk()
        ->assertJson(function (AssertableJson $json) {
            $json
                ->count('data', 10)
                ->where('data.0.type', 'globals')
                ->whereType('data.0.attributes.name', 'string')
                ->etc();
        });
});

it('can filter globals', function ($attribute) {
    $records = GlobalsFactory::new()
        ->withDummyBlueprint()
        ->count(2)
        ->sequence(
            ['name' => 'Foo'],
            ['name' => 'Bar'],
        )
        ->create();

    foreach ($records as $record) {
        getJson('api/globals?' . http_build_query(['filter' => [$attribute => $record->$attribute]]))
            ->assertOk()
            ->assertJson(function (AssertableJson $json) use ($record) {
                $json
                    ->count('data', 1)
                    ->where('data.0.type', 'globals')
                    ->where('data.0.id', $record->getRouteKey())
                    ->where('data.0.attributes.name', $record->name)
                    ->etc();
            });
    }
})->with(['name', 'slug']);

it('can list globals of specific site', function () {
    $site = SiteFactory::new()->createOne();
    GlobalsFactory::new()
        ->withDummyBlueprint()
        ->hasAttached($site)
        ->count(1)
        ->create();
    GlobalsFactory::new()
        ->withDummyBlueprint()
        ->count(2)
        ->create();

    getJson("api/globals?filter[sites.id]={$site->id}")
        ->assertOk()
        ->assertJson(function (AssertableJson $json) {
            $json
                ->count('data', 1)
                ->where('data.0.type', 'globals')
                ->whereType('data.0.attributes.name', 'string')
                ->etc();
        });
});
