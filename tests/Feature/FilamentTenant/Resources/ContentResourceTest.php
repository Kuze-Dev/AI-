<?php

declare(strict_types=1);

use Domain\Content\Database\Factories\ContentFactory;
use Filament\Facades\Filament;

beforeEach(function () {
    testInTenantContext();
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
            'visibility' => 'public',
        ]);

    $result = Filament::getGlobalSearchProvider()
        ->getResults($content->name)
        ->getCategories()['contents']
        ->first();

    expect($result->url)->toEqual(
        route('filament-tenant.resources.contents.edit', $content->getRouteKey())
    );
});
