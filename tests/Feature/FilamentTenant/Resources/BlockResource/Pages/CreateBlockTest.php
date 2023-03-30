<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\BlockResource\Blocks\CreateBlock;
use Domain\Blueprint\Database\Factories\BlueprintFactory;
use Domain\Page\Database\Factories\BlockFactory;
use Domain\Page\Models\Block;
use Filament\Facades\Filament;
use Illuminate\Http\UploadedFile;
use Domain\Blueprint\Enums\FieldType;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext();
    Filament::setContext('filament-tenant');
    loginAsSuperAdmin();
});

it('can render page', function () {
    livewire(CreateBlock::class)
        ->assertFormExists()
        ->assertOk();
});

it('can create block', function () {
    $blueprint = BlueprintFactory::new()
        ->withDummySchema()
        ->createOne();

    livewire(CreateBlock::class)
        ->fillForm([
            'name' => 'Test',
            'component' => 'Test',
            'blueprint_id' => $blueprint->id,
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertOk();

    assertDatabaseHas(Block::class, [
        'name' => 'Test',
        'component' => 'Test',
        'blueprint_id' => $blueprint->id,
    ]);
});

it('can not create block with same name', function () {
    $blueprint = BlueprintFactory::new()
        ->withDummySchema()
        ->createOne();

    BlockFactory::new(['name' => 'Test'])
        ->withDummyBlueprint()
        ->createOne();

    livewire(CreateBlock::class)
        ->fillForm([
            'name' => 'Test',
            'component' => 'Test',
            'blueprint_id' => $blueprint->id,
        ])
        ->call('create')
        ->assertHasFormErrors(['name' => 'unique'])
        ->assertOk();
});

it('can create block with default content', function () {
    $blueprint = BlueprintFactory::new()
        ->addSchemaSection(['title' => 'Main'])
        ->addSchemaField([
            'title' => 'Title',
            'type' => FieldType::TEXT,
        ])
        ->createOne();

    livewire(CreateBlock::class)
        ->fillForm([
            'name' => 'Test',
            'component' => 'Test',
            'blueprint_id' => $blueprint->id,
            'is_fixed_content' => true,
            'data' => ['main' => ['title' => 'Foobar']],
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertOk();

    assertDatabaseHas(Block::class, [
        'name' => 'Test',
        'component' => 'Test',
        'blueprint_id' => $blueprint->id,
        'is_fixed_content' => true,
        'data' => json_encode(['main' => ['title' => 'Foobar']]),
    ]);
});

it('can create block with image', function () {
    $blueprint = BlueprintFactory::new()
        ->withDummySchema()
        ->createOne();

    // Prepare the storage and create a temporary image file.
    $image = UploadedFile::fake()->image('test_image.jpg');

    livewire(CreateBlock::class)
        ->fillForm([
            'name' => 'Test',
            'component' => 'Test',
            'blueprint_id' => $blueprint->id,
            'image' => $image, // Add image to form data.
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertOk();

    // Assert the image exists in the storage and in the image column.
    assertDatabaseHas(Block::class, [
        'name' => 'Test',
        'component' => 'Test',
        'blueprint_id' => $blueprint->id,
    ]);

    assertDatabaseHas(Media::class, [
        'file_name' => $image->getClientOriginalName(),
        'mime_type' => $image->getMimeType(),
    ]);
});
