<?php

declare(strict_types=1);

use Domain\Blueprint\Database\Factories\BlueprintFactory;
use Domain\Blueprint\Enums\FieldType;
use Domain\Content\Database\Factories\ContentEntryFactory;
use Domain\Content\Database\Factories\ContentFactory;
use Filament\Facades\Filament;

beforeEach(function () {
    testInTenantContext();
    loginAsSuperAdmin();
});

it('can globally search', function () {
    $contentEntry = ContentEntryFactory::new()
        ->for(ContentFactory::new()
            ->for(
                BlueprintFactory::new()
                    ->addSchemaSection(['title' => 'Main'])
                    ->addSchemaField(['title' => 'Header', 'type' => FieldType::TEXT])
            ))
        ->createOne(['data' => ['main' => ['header' => 'Foo']]]);

    $result = Filament::getGlobalSearchProvider()
        ->getResults($contentEntry->title)
        ->getCategories()['content entries']
        ->first();

    expect($result->url)->toEqual(
        route('filament-tenant.resources.contents.entries.edit', [
            'ownerRecord' => $contentEntry->content,
            'record' => $contentEntry,
        ])
    );
});
