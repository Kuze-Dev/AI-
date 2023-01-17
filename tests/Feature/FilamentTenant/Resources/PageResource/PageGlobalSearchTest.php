<?php

declare(strict_types=1);

use Filament\Facades\Filament;
use Domain\Page\Database\Factories\PageFactory;

beforeEach(function () {
    testInTenantContext();
    Filament::setContext('filament-tenant');
    loginAsSuperAdmin();
});

it('Page resource must be globaly searchable', function () {

    $data = PageFactory::new()
            ->create();
    
    $results = Filament::getGlobalSearchProvider()
            ->getResults($data->name);

    $this->assertEquals(
        route('filament-tenant.resources.pages.edit',
        $data->getRouteKey()
    ),

        $results->getCategories()['pages']->first()->url
        
    );
   
});

