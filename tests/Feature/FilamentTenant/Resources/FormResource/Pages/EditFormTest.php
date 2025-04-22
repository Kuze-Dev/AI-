<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\FormResource\Pages\EditForm;
use Domain\Form\Database\Factories\FormEmailNotificationFactory;
use Domain\Form\Database\Factories\FormFactory;
use Domain\Form\Models\Form;
use Domain\Form\Models\FormEmailNotification;
use Domain\Internationalization\Database\Factories\LocaleFactory;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext();
    loginAsSuperAdmin();
    LocaleFactory::createDefault();
});

it('can render page', function () {
    /** @var \Domain\Form\Models\Form $form */
    $form = FormFactory::new()
        ->withDummyBlueprint()
        ->has(FormEmailNotificationFactory::new())
        ->createOne();

    livewire(EditForm::class, ['record' => $form->getRouteKey()])
        ->assertSuccessful()
        ->assertFormExists()
        ->assertFormSet([
            'name' => $form->name,
            'form_email_notifications' => $form->formEmailNotifications->toArray(),
        ])
        ->assertOk();
});

it('can edit form', function () {
    $form = FormFactory::new()
        ->withDummyBlueprint()
        ->storeSubmission()
        ->createOne();

    livewire(EditForm::class, ['record' => $form->getRouteKey()])
        ->fillForm([
            'name' => 'Foo',
            'store_submission' => false,
            'form_email_notifications' => [
                [
                    'to' => ['test@user'],
                    'sender' => 'test@user',
                    'sender_name' => 'test user',
                    'subject' => 'Foo Subject',
                    'template' => 'Foo Template',
                    'has_attachments' => false,
                ],
            ],
        ])
        ->call('save')
        ->assertOk()
        ->assertHasNoFormErrors();

    assertDatabaseHas(Form::class, [
        'id' => $form->id,
        'name' => 'Foo',
        'store_submission' => false,
    ]);
    assertDatabaseHas(FormEmailNotification::class, [
        'form_id' => $form->id,
        'to' => ['test@user'],
        'sender' => 'test@user',
        'sender_name' => 'test user',
        'subject' => 'Foo Subject',
        'template' => 'Foo Template',
    ]);
});
