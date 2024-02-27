<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\FormResource\Pages\ListForms;
use Domain\Form\Database\Factories\FormEmailNotificationFactory;
use Domain\Form\Database\Factories\FormFactory;
use Domain\Form\Database\Factories\FormSubmissionFactory;
use Filament\Tables\Actions\DeleteAction;

use function Pest\Laravel\assertModelMissing;
use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext();
    loginAsSuperAdmin();
});
it('can render page', function () {
    livewire(ListForms::class)
        ->assertOk();
});

it('can list forms', function () {
    $forms = FormFactory::new()
        ->withDummyBlueprint()
        ->count(5)
        ->create();

    livewire(ListForms::class)
        ->assertCanSeeTableRecords($forms)
        ->assertOk();
});

it('can delete form', function () {
    $form = FormFactory::new()
        ->withDummyBlueprint()
        ->has(FormEmailNotificationFactory::new())
        ->has(FormSubmissionFactory::new())
        ->createOne();
    $formEmailNotification = $form->formEmailNotifications->first();
    $formSubmission = $form->formSubmissions->first();

    livewire(ListForms::class)->callTableAction(DeleteAction::class, $form);

    assertModelMissing($form);
    assertModelMissing($formEmailNotification);
    assertModelMissing($formSubmission);
});
