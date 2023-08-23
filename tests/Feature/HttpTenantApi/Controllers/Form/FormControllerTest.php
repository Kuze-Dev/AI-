<?php

declare(strict_types=1);

use Illuminate\Testing\Fluent\AssertableJson;

use Domain\Form\Database\Factories\FormFactory;

use Domain\Site\Database\Factories\SiteFactory;

use function Pest\Laravel\getJson;

beforeEach(fn () => testInTenantContext());

it('can list forms', function () {
    FormFactory::new()
        ->withDummyBlueprint()
        ->count(10)
        ->create();

    getJson('api/forms')
        ->assertOk()
        ->assertJson(function (AssertableJson $json) {
            $json->count('data', 10)
                ->where('data.0.type', 'forms')
                ->whereType('data.0.attributes.name', 'string')
                ->etc();
        });
});

it('can list forms with blueprint', function () {
    FormFactory::new()
        ->withDummyBlueprint()
        ->count(10)
        ->create();

    getJson('api/forms?' . http_build_query(['include' => 'blueprint']))
        ->assertOk()
        ->assertJson(function (AssertableJson $json) {
            $json->count('included', 1)
                ->where('included.0.type', 'blueprints')
                ->whereType('included.0.attributes.name', 'string')
                ->whereType('included.0.attributes.schema', 'array')
                ->etc();
        });
});

it('can show form', function () {
    $form = FormFactory::new()
        ->withDummyBlueprint()
        ->createOne();

    getJson('api/forms/' . $form->getRouteKey())
        ->assertOk()
        ->assertJson(function (AssertableJson $json) use ($form) {
            $json->where('data.type', 'forms')
                ->where('data.attributes.name', $form->name)
                ->etc();
        });
});

it('can show form with blueprint', function () {
    $form = FormFactory::new()
        ->withDummyBlueprint()
        ->createOne();

    getJson('api/forms/' . $form->getRouteKey() . '?' . http_build_query(['include' => 'blueprint']))
        ->assertOk()
        ->assertJson(function (AssertableJson $json) use ($form) {
            $json->count('included', 1)
                ->where('included.0.type', 'blueprints')
                ->where('included.0.attributes.name', $form->blueprint->name)
                ->etc();
        });
});

it('can list forms and filter by name', function () {
    FormFactory::new()
        ->withDummyBlueprint()
        ->count(10)
        ->create();

    $form = FormFactory::new()
        ->withDummyBlueprint()
        ->create(['name' => 'Foo']);

    getJson('api/forms?' . http_build_query(['filter' => ['name' => $form->name]]))
        ->assertOk()
        ->assertJson(function (AssertableJson $json) use ($form) {
            $json->count('data', 1)
                ->where('data.0.type', 'forms')
                ->where('data.0.attributes.name', $form->name)
                ->etc();
        });
});

it('can list forms of specific site', function () {
    $site = SiteFactory::new()->createOne();

    FormFactory::new()
        ->withDummyBlueprint()
        ->hasAttached($site)
        ->create();

    FormFactory::new()
        ->withDummyBlueprint()
        ->count(2)
        ->create();

    getJson("api/forms?filter[sites.id]={$site->id}")
        ->assertOk()
        ->assertJson(function (AssertableJson $json) {
            $json->count('data', 1)
                ->where('data.0.type', 'forms')
                ->whereType('data.0.attributes.name', 'string')
                ->etc();
        });
});
