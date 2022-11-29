<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\FormResource\Pages\EditForm;
use Domain\Form\Database\Factories\FormFactory;
use Domain\Form\Models\Form;
use Filament\Facades\Filament;

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
        ->createOne();

    livewire(EditForm::class, ['record' => $form->getRouteKey()])
        ->assertFormExists()
        ->assertSuccessful()
        ->assertFormSet(['name' => $form->name])
        ->assertOk();
});

it('can edit page', function () {
    $form = FormFactory::new()
        ->withDummyBlueprint()
        ->createOne([
            'name' => 'old name',
            'store_submission' => true,
        ]);

    livewire(EditForm::class, ['record' => $form->getRouteKey()])
        ->fillForm([
            'name' => 'new name',
            'store_submission' => false,
        ])
        ->call('save')
        ->assertOk()
        ->assertHasNoFormErrors();

    assertDatabaseHas(Form::class,  [
        'id' => $form->id,
        'name' => 'new name',
        'store_submission' => false,
    ]);
});
