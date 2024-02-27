<?php

declare(strict_types=1);

use App\Features\Customer\AddressBase;
use App\Features\Customer\CustomerBase;
use App\Features\Customer\TierBase;
use App\FilamentTenant\Resources\CustomerResource\Pages\EditCustomer;
use Domain\Customer\Database\Factories\CustomerFactory;
use Domain\Customer\Models\Customer;
use Domain\Tier\Database\Factories\TierFactory;
use Illuminate\Http\UploadedFile;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

uses()->group('customer');

beforeEach(function () {
    testInTenantContext(features: [
        CustomerBase::class,
        AddressBase::class,
        TierBase::class,
    ]);
    loginAsSuperAdmin();
});

it('can render page', function () {
    $tier = TierFactory::createDefault();

    $customer = CustomerFactory::new()
        ->createOne([
            'tier_id' => $tier->getKey(),
        ]);
    livewire(EditCustomer::class, ['record' => $customer->getRouteKey()])
        ->assertSuccessful()
        ->assertFormExists()
        ->assertFormSet([
            'email' => $customer->email,
            'first_name' => $customer->first_name,
            'last_name' => $customer->last_name,
            'mobile' => $customer->mobile,
            'status' => $customer->status->value,
            'birth_date' => $customer->birth_date->format('Y-m-d'),
        ])
        ->assertOk();
});

it('can edit tier', function () {
    $tier = TierFactory::createDefault();

    $customer = CustomerFactory::new()
        ->createOne([
            'tier_id' => $tier->getKey(),
        ]);

    livewire(EditCustomer::class, ['record' => $customer->getRouteKey()])
        ->fillForm([
            'image' => UploadedFile::fake()->image('test_image.jpg'),
            'email' => 'email@test.com',
            'first_name' => 'test first name',
            'last_name' => 'test last name',
            'mobile' => '09123456789',
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
        'status' => $customer->status,
        'birth_date' => now()->subDay()->toDateString().' 00:00:00',
    ]);
});
