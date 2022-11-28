<?php

declare(strict_types=1);

use Domain\Form\Database\Factories\FormFactory;

use Domain\Form\Models\FormSubmission;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\postJson;

beforeEach(fn () => testInTenantContext());

it('submit form', function () {
    $form = FormFactory::new()
        ->withDummyBlueprint()
        ->storeSubmission()
        ->createOne();

    $this->assertDatabaseEmpty(FormSubmission::class);

    postJson(
        'api/form-submissions/'.$form->getRouteKey(),
        [fake()->word() => fake()->sentence(3)]
    )
        ->assertOk()
        ->assertValid();

    assertDatabaseCount(FormSubmission::class, 1);
});
