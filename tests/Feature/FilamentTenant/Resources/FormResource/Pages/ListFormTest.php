<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\FormResource\Pages\ListForms;
use Domain\Form\Database\Factories\FormFactory;
use Filament\Facades\Filament;

use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext();
    Filament::setContext('filament-tenant');
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
