<?php

declare(strict_types=1);

use App\Filament\Resources\TenantResource\Pages\CreateTenant;
use Domain\Tenant\Models\Tenant;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(fn () => loginAsSuperAdmin());

it('can render page', function () {
    livewire(CreateTenant::class)
        ->assertFormExists()
        ->assertSuccessful();
});

it('can create tenant', function () {
    livewire(CreateTenant::class)
        ->fillForm([
            'name' => 'Test',
            Tenant::internalPrefix().'db_host' => 'test',
            Tenant::internalPrefix().'db_port' => '3306 ',
            Tenant::internalPrefix().'db_name' => 'test',
            Tenant::internalPrefix().'db_username' => 'test',
            Tenant::internalPrefix().'db_password' => 'test',

            // TODO: fix domain test
            'domains' => [
                ['domain' => 'test.localhost'],
            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    assertDatabaseHas(Tenant::class, ['name' => 'Test']);
})->todo();
