<?php

declare(strict_types=1);

use Domain\Blueprint\Database\Factories\BlueprintFactory;
use Domain\Blueprint\Enums\FieldType;
use Domain\Taxonomy\Database\Factories\TaxonomyTermFactory;
use Domain\Taxonomy\Database\Factories\TaxonomyFactory;
use Illuminate\Testing\Fluent\AssertableJson;

use function Pest\Laravel\getJson;

beforeEach(function () {
    testInTenantContext();
});

it('fetch list with taxonomy', function () {
    $taxonomy = TaxonomyFactory::new()
        ->for(
            BlueprintFactory::new()
                ->addSchemaSection(['title' => 'Main'])
                ->addSchemaField(['title' => 'Description', 'type' => FieldType::TEXT])
        )
        ->createOne();

    TaxonomyTermFactory::new()->for($taxonomy)->count(10)->create();

    getJson("api/taxonomies/{$taxonomy->getRouteKey()}/terms")
        ->assertOk()
        ->assertJson(function (AssertableJson $json) {
            $json->count('data', 10)
                ->where('data.0.type', 'taxonomyTerms')
                ->whereType('data.0.attributes.name', 'string')
                ->etc();
        });
});

it('fetch show taxonomyTerms with taxonomy', function () {
    $taxonomy = TaxonomyFactory::new()
        ->for(
            BlueprintFactory::new()
                ->addSchemaSection(['title' => 'Main'])
                ->addSchemaField(['title' => 'Description', 'type' => FieldType::TEXT])
        )
        ->createOne();

    $taxonomyTerm = TaxonomyTermFactory::new()->for($taxonomy)->createOne();

    getJson("api/taxonomies/{$taxonomy->getRouteKey()}/terms/{$taxonomyTerm->getRouteKey()}")
        ->assertOk()
        ->assertJson(function (AssertableJson $json) {
            $json->where('data.type', 'taxonomyTerms')
                ->whereType('data.attributes.name', 'string')
                ->etc();
        });
});
