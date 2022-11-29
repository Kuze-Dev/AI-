<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\FormResource\Pages\ViewForms;
use Domain\Form\Database\Factories\FormFactory;
use Filament\Facades\Filament;

use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext();
    Filament::setContext('filament-tenant');
    loginAsSuperAdmin();
});

it('can view page', function () {
    $form = FormFactory::new()
        ->withDummyBlueprint()
        ->createOne();

    livewire(ViewForms::class, ['record' => $form->getRouteKey()])
        ->assertFormSet([
            'name' => $form->name,
            'store_submission' => $form->store_submission,
        ])
        ->assertOk();
});
