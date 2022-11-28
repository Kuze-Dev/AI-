<?php

declare(strict_types=1);

use Domain\Form\Actions\CreateForSubmissionAction;
use Domain\Form\Database\Factories\FormEmailNotificationFactory;
use Domain\Form\Database\Factories\FormFactory;
use Domain\Form\DataTransferObjects\ForSubmissionData;
use Domain\Form\Mail\FormEmailNotificationMail;
use Illuminate\Support\Facades\Mail;

use Domain\Form\Models\FormSubmission;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;

beforeEach(fn () => testInTenantContext());

it('store', function () {
    $form = FormFactory::new()
        ->withDummyBlueprint()
        ->storeSubmission()
        ->createOne();

    $this->assertDatabaseEmpty(FormSubmission::class);

    $data = [fake()->word() => fake()->sentence(3)];

    app(CreateForSubmissionAction::class)
        ->execute(new ForSubmissionData(
            form_id: $form->id,
            data: $data,
        ));

    assertDatabaseCount(FormSubmission::class, 1);
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

    $this->assertDatabaseEmpty(FormSubmission::class);

    app(CreateForSubmissionAction::class)
        ->execute(new ForSubmissionData(
            form_id: $form->id,
            data: ['field' => 'value'],
        ));

    $this->assertDatabaseEmpty(FormSubmission::class);
});

it('dispatch email notifications', function () {
    $form = FormFactory::new()
        ->withDummyBlueprint()
        ->has(FormEmailNotificationFactory::new())
        ->has(FormEmailNotificationFactory::new())
        ->createOne();

    app(CreateForSubmissionAction::class)
        ->execute(new ForSubmissionData(
            form_id: $form->id,
            data: ['field' => 'value'],
        ));

    Mail::assertQueued(FormEmailNotificationMail::class, 2);
});
