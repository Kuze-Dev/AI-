<?php

declare(strict_types=1);

use Filament\Facades\Filament;
use Domain\Taxonomy\Database\Factories\TaxonomyFactory;

beforeEach(function () {
    testInTenantContext();
    Filament::setContext('filament-tenant');
    loginAsSuperAdmin();
});

it('Taxonomy resource must be globaly searchable', function () {

    $data = TaxonomyFactory::new()
            ->create();
    
    $results = Filament::getGlobalSearchProvider()
            ->getResults($data->name);
    
    $this->assertEquals(
        route('filament-tenant.resources.taxonomies.edit',
        $data->getRouteKey()
    ),

        $results->getCategories()['taxonomies']->first()->url
        
    );
   
});

