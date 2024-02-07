<?php

declare(strict_types=1);

use App\Features\Customer\AddressBase;
use App\Features\Customer\CustomerBase;
use App\Features\Customer\TierBase;
use Domain\Address\Database\Factories\AddressFactory;
use Domain\Address\Database\Factories\StateFactory;
use Domain\Address\Models\Address;
use Domain\Customer\Database\Factories\CustomerFactory;
use Illuminate\Testing\Fluent\AssertableJson;
use Laravel\Sanctum\Sanctum;
use Tests\RequestFactories\AddressRequestFactory;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\deleteJson;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;
use function Pest\Laravel\putJson;
use function PHPUnit\Framework\assertCount;
use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertTrue;

uses()->group('customer');

beforeEach(function () {
    $tenant = testInTenantContext();
    $tenant->features()->activate(CustomerBase::class);
    $tenant->features()->activate(AddressBase::class);
    $tenant->features()->activate(TierBase::class);

    $this->customer = CustomerFactory::new()
        ->hasAddress()
        ->createOne();

    Sanctum::actingAs($this->customer);
});

it('can list all addresses', function () {
    AddressFactory::new([
        'customer_id' => $this->customer->getKey(),
    ])
        ->count(5)
        ->create();

    $params = [
        'include' => 'state.country',
    ];

    getJson('api/addresses?'.http_build_query($params, '', ','))
        ->assertOk()
        ->assertJson(function (AssertableJson $json) {
            $json->count('included', 12)
                ->whereAll([
                    'included.0.type' => 'states',
                    'included.1.type' => 'countries',

                ])
                ->etc();
        });
});

it('can create 1st address and set all default', function (?bool $default) {
    $newData = AddressRequestFactory::new()
        ->withState(StateFactory::new()->createOne())
        ->defaultShipping($default)
        ->defaultBilling($default)
        ->create();

    Address::truncate();
    assertCount(0, $this->customer->refresh()->addresses);

    postJson('api/addresses', $newData)
        ->assertValid()
        ->assertCreated();

    assertCount(1, $this->customer->refresh()->addresses);

    unset($newData['country_id']);

    assertDatabaseHas(Address::class, [
        'customer_id' => $this->customer->getKey(),
        ...$newData,
        'is_default_shipping' => true,
        'is_default_billing' => true,
    ]);
})
    ->with(
        [
            'default' => true,
            'not default' => true,
            // 'null' => null,
        ]
    );

it('can create then replace new default', function () {
    $newData = AddressRequestFactory::new()
        ->withState(StateFactory::new()->createOne())
        ->defaultShipping()
        ->defaultBilling()
        ->create();

    /** @var \Domain\Address\Models\Address[] $existingAddresses */
    $existingAddresses = $this->customer->refresh()->addresses;
    assertCount(1, $existingAddresses);
    assertTrue($existingAddresses[0]->is_default_billing);
    assertTrue($existingAddresses[0]->is_default_shipping);

    postJson('api/addresses', $newData)
        ->assertValid()
        ->assertCreated();

    $existingAddresses[0]->refresh();
    assertFalse($existingAddresses[0]->is_default_billing);
    assertFalse($existingAddresses[0]->is_default_shipping);

    unset($newData['country_id']);

    assertDatabaseHas(Address::class, [
        'customer_id' => $this->customer->getKey(),
        ...$newData,
    ]);
});

it('can edit address', function () {

    Address::truncate();

    $state = StateFactory::new()->createOne();
    $address = AddressFactory::new()
        ->createOne([
            'customer_id' => $this->customer->id,
            'state_id' => $state->getKey(),
            'address_line_1' => 'old-address',
            'city' => 'old-city',
            'zip_code' => '4321',
            'label_as' => 'home',
            'is_default_shipping' => false,
            'is_default_billing' => false,
        ]);

    $state1 = StateFactory::new()->createOne();

    $newData = [
        'country_id' => $state1->country->code,
        'state_id' => $state1->getKey(),
        'address_line_1' => 'new-address',
        'city' => 'new-city',
        'zip_code' => '1234',
        'label_as' => 'home',
        'is_default_shipping' => false,
        'is_default_billing' => false,
    ];

    putJson('api/addresses/'.$address->getKey(), $newData)
        ->assertValid()
        ->assertOk();

    assertDatabaseHas(Address::class, [
        'customer_id' => $this->customer->id,
        'state_id' => $newData['state_id'],
        'address_line_1' => $newData['address_line_1'],
        'city' => $newData['city'],
        'zip_code' => $newData['zip_code'],
        'label_as' => $newData['label_as'],
        'is_default_shipping' => $newData['is_default_shipping'],
        'is_default_billing' => $newData['is_default_billing'],
    ]);

});

it('can delete address', function () {
    $address = AddressFactory::new()
        ->createOne([
            'customer_id' => $this->customer->id,
            'is_default_shipping' => false,
            'is_default_billing' => false,
        ]);

    deleteJson('api/addresses/'.$address->id)->assertValid();
    $this->assertDatabaseMissing('addresses', ['id' => $address->id]);
});
