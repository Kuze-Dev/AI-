<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\SliceResource\Slices\CreateSlice;
use Domain\Blueprint\Database\Factories\BlueprintFactory;
use Domain\Page\Database\Factories\SliceFactory;
use Domain\Page\Models\Slice;
use Filament\Facades\Filament;
use Domain\Blueprint\Enums\FieldType;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext();
    Filament::setContext('filament-tenant');
    loginAsSuperAdmin();
});

it('can render page', function () {
    livewire(CreateSlice::class)
        ->assertFormExists()
        ->assertOk();
});

it('can create slice', function () {
    $blueprint = BlueprintFactory::new()
        ->withDummySchema()
        ->createOne();

    livewire(CreateSlice::class)
        ->fillForm([
            'name' => 'Test',
            'component' => 'Test',
            'blueprint_id' => $blueprint->id,
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertOk();

    assertDatabaseHas(Slice::class, [
        'name' => 'Test',
        'component' => 'Test',
        'blueprint_id' => $blueprint->id,
    ]);
});

it('can not create slice with same name', function () {
    $blueprint = BlueprintFactory::new()
        ->withDummySchema()
        ->createOne();

    SliceFactory::new(['name' => 'Test'])
        ->withDummyBlueprint()
        ->createOne();

    livewire(CreateSlice::class)
        ->fillForm([
            'name' => 'Test',
            'component' => 'Test',
            'blueprint_id' => $blueprint->id,
        ])
        ->call('create')
        ->assertHasFormErrors(['name' => 'unique'])
        ->assertOk();
});

it('can create slice with default content', function () {
    $blueprint = BlueprintFactory::new()
        ->addSchemaSection(['title' => 'Main'])
        ->addSchemaField([
            'title' => 'Title',
            'type' => FieldType::TEXT,
        ])
        ->createOne();

    livewire(CreateSlice::class)
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

    assertDatabaseHas(Slice::class, [
        'name' => 'Test',
        'component' => 'Test',
        'blueprint_id' => $blueprint->id,
        'is_fixed_content' => true,
        'data' => json_encode(['main' => ['title' => 'Foobar']]),
    ]);
});

it('can create slice with image', function () {
    $blueprint = BlueprintFactory::new()
        ->withDummySchema()
        ->createOne();

    // Prepare the storage and create a temporary image file.
    Storage::fake('public');
    $image = UploadedFile::fake()->image('test_image.jpg');

    livewire(CreateSlice::class)
        ->fillForm([
            'name' => 'Test',
            'component' => 'Test',
            'blueprint_id' => $blueprint->id,
            'image' => $image, // Add image to form data.
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertOk();

    // Retrieve the created slice to get the stored image path.
    $createdSlice = Slice::where([
        'name' => 'Test',
        'component' => 'Test',
        'blueprint_id' => $blueprint->id,
    ])->first();

    // Assert the image exists in the storage and in the image column.
    Storage::disk('public')->assertExists($createdSlice->image);
    assertDatabaseHas(Slice::class, [
        'name' => 'Test',
        'component' => 'Test',
        'blueprint_id' => $blueprint->id,
        'image' => $createdSlice->image,
    ]);
});
