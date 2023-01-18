<?php

declare(strict_types=1);

use Filament\Facades\Filament;
use Domain\Form\Database\Factories\FormFactory;

beforeEach(function () {
    testInTenantContext();
    Filament::setContext('filament-tenant');
    loginAsSuperAdmin();
});

it('form resource must be globaly searchable', function () {
    $data = FormFactory::new()
        ->withDummyBlueprint()
        ->create();

    $results = Filament::getGlobalSearchProvider()
        ->getResults($data->name);

    expect(
        route('filament-tenant.resources.forms.edit', $data->getRouteKey())
    )->toEqual(
        $results->getCategories()['forms']->first()->url
    );
});
