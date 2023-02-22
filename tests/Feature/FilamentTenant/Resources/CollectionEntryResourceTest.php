<?php

declare(strict_types=1);

use Filament\Facades\Filament;
use Domain\Blueprint\Database\Factories\BlueprintFactory;
use Domain\Blueprint\Enums\FieldType;
use Domain\Collection\Database\Factories\CollectionFactory;
use Domain\Collection\Database\Factories\CollectionEntryFactory;

beforeEach(function () {
    testInTenantContext();
    Filament::setContext('filament-tenant');
    loginAsSuperAdmin();
});

it('can globally search', function () {
    $collectionEntry = CollectionEntryFactory::new()
        ->for(CollectionFactory::new()
            ->for(
                BlueprintFactory::new()
                    ->addSchemaSection(['title' => 'Main'])
                    ->addSchemaField(['title' => 'Header', 'type' => FieldType::TEXT])
            ))
        ->createOne(['data' => ['main' => ['header' => 'Foo']]]);

    $result = Filament::getGlobalSearchProvider()
        ->getResults($collectionEntry->title)
        ->getCategories()['collection entries']
        ->first();

    expect($result->url)->toEqual(
        route('filament-tenant.resources.collections.entries.edit', [
            'ownerRecord' => $collectionEntry->collection,
            'record' => $collectionEntry,
        ])
    );
});
