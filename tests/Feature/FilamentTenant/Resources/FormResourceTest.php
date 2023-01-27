<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\FormResource;
use Filament\Facades\Filament;
use Domain\Form\Database\Factories\FormFactory;

beforeEach(function () {
    testInTenantContext();
    Filament::setContext('filament-tenant');
    loginAsSuperAdmin();
});

it('can globally search', function () {
    $form = FormFactory::new()
        ->withDummyBlueprint()
        ->createOne();

    $results = Filament::getGlobalSearchProvider()
        ->getResults($form->name);

    expect($results->getCategories()['forms']->first()->url)
        ->toEqual(FormResource::getUrl('edit', [$form]));
});
