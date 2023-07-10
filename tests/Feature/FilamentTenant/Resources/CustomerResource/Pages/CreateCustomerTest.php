<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\CustomerResource\Pages\CreateCustomer;
use Domain\Customer\Enums\Status;
use Domain\Customer\Models\Customer;
use Domain\Tier\Models\Tier;
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

    livewire(CreateCustomer::class)
        ->fillForm([
            'tier_id' => Tier::first()->getKey(),
            'image' => UploadedFile::fake()->image('test_image.jpg'),
            'email' => 'email@test.com',
            'first_name' => 'test first name',
            'last_name' => 'test last name',
            'mobile' => '09123456789',
            'status' => Status::ACTIVE->value,
            'birth_date' => now()->subDay()->format('Y-m-d'),
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
        'birth_date' => now()->subDay()->toDateString().' 00:00:00',
    ]);
});
