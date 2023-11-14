<?php

declare(strict_types=1);

use App\Features\PaymentGateway\OfflineGateway;
use App\FilamentTenant\Resources\PaymentMethodResource\Pages\CreatePaymentMethod;
use Domain\PaymentMethod\Models\PaymentMethod;
use Filament\Facades\Filament;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext();
    Filament::setContext('filament-tenant');
    loginAsSuperAdmin();

    tenancy()->tenant->features()->activate(OfflineGateway::class);
});

it('can render payment method', function () {
    livewire(CreatePaymentMethod::class)
        ->assertFormExists()
        ->assertSuccessful();
});

it('can create payment method', function () {

    livewire(CreatePaymentMethod::class)
        ->fillForm([
            'title' => 'COD',
            'subtitle' => 'Cash on Delivery (Cod)',
            'status' => true,
            'gateway' => 'manual',
        ])->call('create')
        ->assertHasNoFormErrors()
        ->assertOk()
        ->instance()
        ->record;

    assertDatabaseHas(PaymentMethod::class, [
        'title' => 'COD',
        'subtitle' => 'Cash on Delivery (Cod)',
        'gateway' => 'manual',

    ]);
});
