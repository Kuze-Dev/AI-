<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\FormResource\Pages\EditForm;
use Domain\Form\Database\Factories\FormEmailNotificationFactory;
use Domain\Form\Database\Factories\FormFactory;
use Domain\Form\Models\Form;
use Domain\Form\Models\FormEmailNotification;
use Filament\Facades\Filament;

use function Pest\Faker\faker;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext();
    Filament::setContext('filament-tenant');
    loginAsSuperAdmin();
});

it('can render page', function () {
    /** @var \Domain\Form\Models\Form $form */
    $form = FormFactory::new()
        ->withDummyBlueprint()
        ->has(FormEmailNotificationFactory::new())
        ->createOne();

    livewire(EditForm::class, ['record' => $form->getRouteKey()])
        ->assertFormExists()
        ->assertSuccessful()
        ->assertFormSet([
            'name' => $form->name,
            'formEmailNotifications' => ['record-1' => $form->formEmailNotifications->first()->toArray()],
        ])
        ->assertOk();
});

it('can edit page', function () {
    $form = FormFactory::new()
        ->withDummyBlueprint()
        ->createOne([
            'name' => 'old name',
            'store_submission' => true,
        ]);

    $formEmailNotification = [
        'recipient' => faker()->safeEmail(),
        'cc' => faker()->safeEmail(),
        'bcc' => faker()->safeEmail(),
        'reply_to' => faker()->safeEmail(),
        'sender' => faker()->safeEmail(),
        'template' => faker()->safeEmail(),
    ];

    assertDatabaseCount(Form::class, 1);
    $this->assertDatabaseEmpty(FormEmailNotification::class);

    livewire(EditForm::class, ['record' => $form->getRouteKey()])
        ->fillForm([
            'name' => 'new name',
            'store_submission' => false,
            'formEmailNotifications' => [$formEmailNotification],
        ])
        ->call('save')
        ->assertOk()
        ->assertHasNoFormErrors();

    assertDatabaseHas(Form::class,  [
        'id' => $form->id,
        'name' => 'new name',
        'store_submission' => false,
    ]);

    $this->markTestSkipped('filament bugs on getting latest data on relationships.');

    assertDatabaseHas(FormEmailNotification::class,  [
        'form_id' => $form->id,
        ...$formEmailNotification,
    ]);
});
