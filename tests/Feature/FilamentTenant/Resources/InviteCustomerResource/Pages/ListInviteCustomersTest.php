<?php

declare(strict_types=1);

use App\Features\Customer\AddressBase;
use App\Features\Customer\CustomerBase;
use App\Features\Customer\TierBase;
use App\FilamentTenant\Resources\InviteCustomerResource\Pages\ListInviteCustomers;
use App\Settings\FormSettings;
use Domain\Customer\Database\Factories\CustomerFactory;
use Domain\Customer\Enums\RegisterStatus;
use Domain\Customer\Jobs\CustomerSendInvitationJob;
use Domain\Tier\Database\Factories\TierFactory;
use Domain\Tier\Models\Tier;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Queue;

use function Pest\Livewire\livewire;

beforeEach(function () {
    $tenant = testInTenantContext();
    $tenant->features()->activate(CustomerBase::class);
    $tenant->features()->activate(AddressBase::class);
    $tenant->features()->activate(TierBase::class);
    Filament::setContext('filament-tenant');
    if (Tier::whereName(config('domain.tier.default'))->doesntExist()) {
        TierFactory::createDefault();
    }
    loginAsSuperAdmin();
});

it('can render page', function () {
    livewire(ListInviteCustomers::class)
        ->assertOk();
});

it('can dispatch bulk send-register-invitation job', function () {

    $customers = CustomerFactory::new()
        ->unregistered()
        ->count(2)
        ->create();

    FormSettings::fake([
        'sender_email' => fake()->safeEmail(),
    ]);

    Queue::fake();

    livewire(ListInviteCustomers::class)
        ->callTableBulkAction('send-register-invitation', $customers);

    Queue::assertPushed(
        CustomerSendInvitationJob::class,
        1
    );
});

it('can dispatch all send-register-invitation job', function () {

    CustomerFactory::new()
        ->unregistered()
        ->count(2)
        ->create();

    FormSettings::fake([
        'sender_email' => fake()->safeEmail(),
    ]);

    Queue::fake();

    livewire(ListInviteCustomers::class)
        ->callPageAction(
            'send-register-invitation',
            data: ['register_status' => collect(RegisterStatus::allowedResendInviteCases())
                ->map(fn (RegisterStatus $status) => $status->value)
                ->toArray()]
        )
        ->assertHasNoErrors()
        ->assertOk();

    Queue::assertPushed(
        CustomerSendInvitationJob::class,
        1
    );
});
