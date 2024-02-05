<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\FormResource;
use Domain\Form\Database\Factories\FormFactory;
use Filament\Facades\Filament;

beforeEach(function () {
    testInTenantContext();
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
