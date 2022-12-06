<?php

declare(strict_types=1);

use Domain\Form\Database\Factories\FormFactory;

use Illuminate\Testing\Fluent\AssertableJson;

use function Pest\Laravel\getJson;

beforeEach(fn () => testInTenantContext());

it('fetch list with blueprint', function () {
    FormFactory::new()
        ->withDummyBlueprint()
        ->count(10)
        ->create();

    getJson('api/forms?'.http_build_query(['include' => 'blueprint']))
        ->assertOk()
        ->assertJson(function (AssertableJson $json) {
            $json
                ->count('data', 10)
                ->where('data.0.type', 'forms')
                ->whereType('data.0.attributes.name', 'string')
                ->count('included', 1)
                ->where('included.0.type', 'blueprints')
                ->whereType('included.0.attributes.name', 'string')
                ->whereType('included.0.attributes.schema', 'array')
                ->etc();
        });
});

it('fetch show with blueprint', function () {
    $form = FormFactory::new()
        ->withDummyBlueprint()
        ->createOne();

    getJson('api/forms/'.$form->getRouteKey().'?'.http_build_query(['include' => 'blueprint']))
        ->assertOk()
        ->assertJson(function (AssertableJson $json) use ($form) {
            $json
                ->where('data.type', 'forms')
                ->where('data.attributes.name', $form->name)
                ->count('included', 1)
                ->where('included.0.type', 'blueprints')
                ->where('included.0.attributes.name', $form->blueprint->name)
                ->etc();
        });
});
