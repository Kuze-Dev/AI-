<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\PageResource;
use Domain\Page\Database\Factories\PageFactory;
use Filament\Facades\Filament;

beforeEach(function () {
    testInTenantContext();
    loginAsSuperAdmin();
});

it('can globally search', function () {
    $page = PageFactory::new()
        ->createOne();

    $results = Filament::getGlobalSearchProvider()
        ->getResults($page->name);

    expect($results->getCategories()['pages']->first()->url)
        ->toEqual(PageResource::getUrl('edit', [$page]));
});
