<?php

declare(strict_types=1);

use App\Features\Customer\AddressBase;
use App\Features\Customer\CustomerBase;
use App\Features\Customer\TierBase;
use App\FilamentTenant\Resources\InviteCustomerResource\Pages\CreateInviteCustomer;
use Domain\Address\Database\Factories\StateFactory;
use Domain\Customer\Enums\RegisterStatus;
use Domain\Customer\Enums\Status;
use Domain\Customer\Models\Customer;
use Domain\Tier\Database\Factories\TierFactory;
use Domain\Tier\Enums\TierApprovalStatus;
use Domain\Tier\Models\Tier;
use Filament\Facades\Filament;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Event;
use Tests\RequestFactories\CustomerRequestFactory;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\travelTo;
use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext(features: [
        CustomerBase::class,
        AddressBase::class,
        TierBase::class,
    ]);
    if (Tier::whereName(config('domain.tier.default'))->doesntExist()) {
        TierFactory::createDefault();
    }
    loginAsSuperAdmin();
});

it('can render page', function () {
    livewire(CreateInviteCustomer::class)
        ->assertFormExists()
        ->assertOk();
});

it('can create customer'/* w/ same address'*/, function () {

    $data = CustomerRequestFactory::new()
        ->withTier(Tier::first())
        ->withShippingAddress(StateFactory::new()->createOne())
        ->withBillingSameAsShipping()
        ->create();

    // to get latest customer
    travelTo(now()->addSecond());

    Event::fake(Registered::class);

    livewire(CreateInviteCustomer::class)
        ->fillForm($data)
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertOk();

    Event::assertNotDispatched(Registered::class);

    $customer = Customer::latest()->first();

    assertDatabaseHas(Customer::class, [
        'id' => $customer->getKey(),
        'email' => $data['email'],
        'first_name' => $data['first_name'],
        'last_name' => $data['last_name'],
        'mobile' => $data['mobile'],
        'gender' => $data['gender'],
        'status' => Status::INACTIVE,
        'register_status' => RegisterStatus::UNREGISTERED,
        'tier_approval_status' => TierApprovalStatus::APPROVED,
        'birth_date' => $data['birth_date'].' 00:00:00',
    ]);

    // assertEquals( $data['birth_date'],
    // now()->createFromDate($data['birth_date'])->timezone(Filament::auth()->user()->timezone)->format('Y-m-d'));
});
