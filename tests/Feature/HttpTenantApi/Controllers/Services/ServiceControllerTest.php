<?php

declare(strict_types=1);

use App\Features\Service\ServiceBase;
use Domain\Service\Databases\Factories\ServiceFactory;
use Domain\Taxonomy\Database\Factories\TaxonomyFactory;
use Domain\Taxonomy\Database\Factories\TaxonomyTermFactory;
use Illuminate\Testing\Fluent\AssertableJson;

use function Pest\Laravel\getJson;

beforeEach(function () {
    testInTenantContext();

    tenancy()->tenant->features()->activate(ServiceBase::class);
});

it('can list services', function () {
    ServiceFactory::new(['status' => 1])
        ->has(TaxonomyTermFactory::new()
            ->for(TaxonomyFactory::new()
                ->withDummyBlueprint())
            ->count(2))
        ->withDummyBlueprint()
        ->count(10)
        ->create();

    getJson('/api/services')
        ->assertOk()
        ->assertJson(function (AssertableJson $json) {
            $json->count('data', 10)
                ->where('data.0.type', 'services')
                ->whereType('data.0.attributes.name', 'string')
                ->etc();
        });
});
it('can show a service', function () {
    $service = ServiceFactory::new(['status' => 1])
        ->has(TaxonomyTermFactory::new()
            ->for(TaxonomyFactory::new()
                ->withDummyBlueprint())
            ->count(2))
        ->withDummyBlueprint()
        ->createOne();

    getJson('api/services/' . $service->getRouteKey())
        ->assertOk()
        ->assertJson(function (AssertableJson $json) use ($service) {
            $json
                ->where('data.type', 'services')
                ->where('data.id', (string) $service->getRouteKey())
                ->where('data.attributes.name', $service->name)
                ->etc();
        });
});

it("can't list inactive services", function () {
    ServiceFactory::new(['status' => 0])
        ->has(TaxonomyTermFactory::new()
            ->for(TaxonomyFactory::new()
                ->withDummyBlueprint())
            ->count(2))
        ->withDummyBlueprint()
        ->count(10)
        ->create();

    getJson('api/services')
        ->assertOk()
        ->assertJson(function (AssertableJson $json) {
            $json
                ->count('data', 0)
                ->etc();
        });
});

it("can't show an inactive service", function () {
    $service = ServiceFactory::new(['status' => 0])
        ->has(TaxonomyTermFactory::new()
            ->for(TaxonomyFactory::new()
                ->withDummyBlueprint())
            ->count(2))
        ->withDummyBlueprint()
        ->createOne();

    getJson("api/services/{$service->getRouteKey()}")
        ->assertStatus(404);
});
