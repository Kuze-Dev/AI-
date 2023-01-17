<?php

declare(strict_types=1);

use Filament\Facades\Filament;
use Domain\Form\Database\Factories\FormFactory;

beforeEach(function () {
    testInTenantContext();
    Filament::setContext('filament-tenant');
    loginAsSuperAdmin();
});

it('blueprint must have a getGloballySearchableAttribute', function () {

    $data = FormFactory::new()
            ->withDummyBlueprint()
            ->create();
    
    $results = Filament::getGlobalSearchProvider()
            ->getResults($data->name);

    $this->assertEquals(
        route('filament-tenant.resources.forms.edit',
        $data->getRouteKey()
    ),

        $results->getCategories()['forms']->first()->url
        
    );
   
})->only();

