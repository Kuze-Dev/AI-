<?php

declare(strict_types=1);

use Domain\Form\Actions\CreateFormSubmissionAction;
use Domain\Form\Database\Factories\FormEmailNotificationFactory;
use Domain\Form\Database\Factories\FormFactory;
use Domain\Form\Mail\FormEmailNotificationMail;
use Domain\Form\Models\FormSubmission;
use Illuminate\Support\Facades\Mail;

use function Pest\Laravel\assertDatabaseEmpty;
use function Pest\Laravel\assertDatabaseHas;

beforeEach(fn () => testInTenantContext());

it('store', function () {
    $form = FormFactory::new()
        ->withDummyBlueprint()
        ->storeSubmission()
        ->createOne();

    $data = [fake()->word() => fake()->sentence(3)];

    app(CreateFormSubmissionAction::class)
        ->execute(
            form: $form,
            data: $data,
        );

    assertDatabaseHas(FormSubmission::class, [
        'form_id' => $form->id,
        'data' => json_encode($data),
    ]);
});

it('do not store', function () {
    $form = FormFactory::new()
        ->withDummyBlueprint()
        ->storeSubmission(false)
        ->createOne();

    assertDatabaseEmpty(FormSubmission::class);

    app(CreateFormSubmissionAction::class)
        ->execute(
            form: $form,
            data: ['field' => 'value'],
        );

    assertDatabaseEmpty(FormSubmission::class);
});

it('dispatch email notifications', function () {
    $form = FormFactory::new()
        ->withDummyBlueprint()
        ->has(FormEmailNotificationFactory::new())
        ->has(FormEmailNotificationFactory::new())
        ->createOne();

    app(CreateFormSubmissionAction::class)
        ->execute(
            form: $form,
            data: ['field' => 'value'],
        );

    Mail::assertQueued(FormEmailNotificationMail::class, 2);
});
