<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\CustomerResource\Pages\CreateCustomer;
use Domain\Customer\Enums\Status;
use Domain\Customer\Models\Customer;
use Filament\Facades\Filament;

use Illuminate\Http\UploadedFile;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext();
    Filament::setContext('filament-tenant');
    loginAsSuperAdmin();
});

it('can render page', function () {
    livewire(CreateCustomer::class)
        ->assertFormExists()
        ->assertOk();
});

it('can create customer', function () {

    // Prepare the storage and create a temporary image file.
    $image = UploadedFile::fake()->image('test_image.jpg');

    livewire(CreateCustomer::class)
        ->fillForm([
            'image' => $image,
            'email' => 'email@test.com',
            'first_name' => 'test first name',
            'last_name' => 'test last name',
            'mobile' => '09123456789',
            'status' => true,
            'birth_date' => now()->subDay(),
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertOk();

    assertDatabaseHas(Customer::class, [
        'email' => 'email@test.com',
        'first_name' => 'test first name',
        'last_name' => 'test last name',
        'mobile' => '09123456789',
        'status' => Status::ACTIVE->value,
        'birth_date' => now()->subDay(),
    ]);
});
