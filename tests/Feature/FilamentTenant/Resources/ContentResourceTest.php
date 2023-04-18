<?php

declare(strict_types=1);

use Filament\Facades\Filament;
use Domain\Content\Database\Factories\ContentFactory;

beforeEach(function () {
    testInTenantContext();
    Filament::setContext('filament-tenant');
    loginAsSuperAdmin();
});

it('can globally search', function () {
    $content = ContentFactory::new()
        ->withDummyBlueprint()
        ->createOne([
            'name' => 'Test Content',
            'future_publish_date_behavior' => 'private',
            'past_publish_date_behavior' => 'unlisted',
            'is_sortable' => true,
        ]);

    $result = Filament::getGlobalSearchProvider()
        ->getResults($content->name)
        ->getCategories()['contents']
        ->first();

    expect($result->url)->toEqual(
        route('filament-tenant.resources.contents.edit', $content->getRouteKey())
    );
});
