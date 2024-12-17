<?php

declare(strict_types=1);

use App\Features\Service\ServiceBase;
use Domain\Service\Databases\Factories\ServiceFactory;
use Domain\Taxonomy\Database\Factories\TaxonomyFactory;
use Domain\Taxonomy\Database\Factories\TaxonomyTermFactory;
use Illuminate\Testing\Fluent\AssertableJson;

use function Pest\Laravel\getJson;

beforeEach(function () {
    testInTenantContext()->features()->activate(ServiceBase::class);
});

it('can list services', function () {
    ServiceFactory::new()
        ->has(TaxonomyTermFactory::new()
            ->for(TaxonomyFactory::new()
                ->withDummyBlueprint()))
        ->withDummyBlueprint()
        ->isActive()
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
    $service = ServiceFactory::new()
        ->has(TaxonomyTermFactory::new()
            ->for(TaxonomyFactory::new()
                ->withDummyBlueprint()))
        ->withDummyBlueprint()
        ->isActive()
        ->createOne();

    getJson('api/services/'.$service->getRouteKey())
        ->assertOk()
        ->assertJson(function (AssertableJson $json) use ($service) {
            $json
                ->where('data.type', 'services')
                ->where('data.id', (string) $service->getRouteKey())
                ->where('data.attributes.name', $service->name)
                ->etc();
        });
});

it('can filter services', function ($attribute) {
    $services = ServiceFactory::new()
        ->has(TaxonomyTermFactory::new()
            ->for(TaxonomyFactory::new()
                ->withDummyBlueprint())
            ->count(2))
        ->withDummyBlueprint()
        ->isActive()
        ->count(1)
        ->create();

    foreach ($services as $service) {
        getJson('api/services?'.http_build_query([
            'filter' => [$attribute => $service->$attribute],
        ]))
            ->assertOk()
            ->assertJson(function (AssertableJson $json) use ($service) {
                $json
                    ->where('data.0.type', 'services')
                    ->where('data.0.id', (string) $service->getRouteKey())
                    ->where('data.0.attributes.name', $service->name)
                    ->count('data', 1)
                    ->etc();
            });
    }
})->with([
    'name',
    'retail_price',
    'selling_price',
    'is_featured',
    'is_special_offer',
    'pay_upfront',
    'is_subscription',
    'status',
    'needs_approval',
    'is_auto_generated_bill',
    //    'is_partial_payment',
    'is_installment',
    'taxonomies',
]);

it("can't list inactive services", function () {
    ServiceFactory::new()
        ->has(TaxonomyTermFactory::new()
            ->for(TaxonomyFactory::new()
                ->withDummyBlueprint())
            ->count(2))
        ->withDummyBlueprint()
        ->isActive(false)
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
    $service = ServiceFactory::new()
        ->has(TaxonomyTermFactory::new()
            ->for(TaxonomyFactory::new()
                ->withDummyBlueprint()))
        ->withDummyBlueprint()
        ->isActive(false)
        ->createOne();

    getJson("api/services/{$service->getRouteKey()}")->assertNotFound();
});
