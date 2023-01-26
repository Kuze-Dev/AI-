<?php

declare(strict_types=1);
use Domain\Taxonomy\Database\Factories\TaxonomyFactory;
use Illuminate\Testing\Fluent\AssertableJson;

use function Pest\Laravel\getJson;

beforeEach(function () {
    testInTenantContext();
});
it('return list', function () {
    TaxonomyFactory::new()
        ->count(10)
        ->create();
    getJson('api/taxonomies')
        ->assertOk()
        ->assertJson(function (AssertableJson $json) {
            $json
                ->count('data', 10)
                ->where('data.0.type', 'taxonomies')
                ->whereType('data.0.attributes.name', 'string')
                ->etc();
        });
});

it('show', function () {
    $taxonomy = TaxonomyFactory::new()
        ->createOne(['name' => 'My Taxonomy Title']);

    getJson('api/taxonomies/'.$taxonomy->getRouteKey())
        ->assertOk()
        ->assertJson(function (AssertableJson $json) use ($taxonomy) {
            $json
                ->where('data.type', 'taxonomies')
                ->where('data.id', Str::slug($taxonomy->name))
                ->where('data.attributes.name', $taxonomy->name)
                ->etc();
        });
});

it('filter', function () {
    $taxonomies = TaxonomyFactory::new()
        ->count(2)
        ->sequence(
            ['name' => 'page 1'],
            ['name' => 'page 2'],
        )
        ->create();

    foreach ($taxonomies as $taxonomy) {
        getJson('api/taxonomies?'.http_build_query(['filter' => ['name' => $taxonomy->name]]))
            ->assertOk()
            ->assertJson(function (AssertableJson $json) use ($taxonomy) {
                $json
                    ->count('data', 1)
                    ->where('data.0.type', 'taxonomies')
                    ->where('data.0.id', $taxonomy->getRouteKey())
                    ->where('data.0.attributes.name', $taxonomy->name)
                    ->etc();
            });
        getJson('api/taxonomies?'.http_build_query(['filter[slug]' => $taxonomy->slug]))
            ->assertOk()
            ->assertJson(function (AssertableJson $json) use ($taxonomy) {
                $json
                    ->count('data', 1)
                    ->where('data.0.type', 'taxonomies')
                    ->where('data.0.id', $taxonomy->getRouteKey())
                    ->where('data.0.attributes.name', $taxonomy->name)
                    ->etc();
            });
    }
});
