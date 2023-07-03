<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\CustomerResource\Pages\EditCustomer;
use Domain\Customer\Database\Factories\CustomerFactory;
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
    $customer = CustomerFactory::new()
        ->createOne();

    livewire(EditCustomer::class, ['record' => $customer->getRouteKey()])
        ->assertSuccessful()
        ->assertFormExists()
        ->assertFormSet([
            'email' => $customer->email,
            'first_name' => $customer->first_name,
            'last_name' => $customer->last_name,
            'mobile' => $customer->mobile,
            'status' => $customer->status->value,
            'birth_date' => $customer->birth_date,
        ])
        ->assertOk();
});

it('can edit tier', function () {
    $customer = CustomerFactory::new()
        ->createOne();

    livewire(EditCustomer::class, ['record' => $customer->getRouteKey()])
        ->fillForm([
            'image' => UploadedFile::fake()->image('test_image.jpg'),
            'email' => 'email@test.com',
            'first_name' => 'test first name',
            'last_name' => 'test last name',
            'mobile' => '09123456789',
            'status' => true,
            'birth_date' => now()->subDay(),
        ])
        ->call('save')
        ->assertOk()
        ->assertHasNoFormErrors();

    assertDatabaseHas(Customer::class, [
        'email' => 'email@test.com',
        'first_name' => 'test first name',
        'last_name' => 'test last name',
        'mobile' => '09123456789',
        'status' => Status::ACTIVE->value,
        'birth_date' => now()->subDay()->toDateString().' 00:00:00',
    ]);
});
