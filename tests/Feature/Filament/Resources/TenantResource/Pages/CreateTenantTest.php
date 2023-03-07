<?php

declare(strict_types=1);

use App\Filament\Resources\TenantResource\Pages\CreateTenant;
use Domain\Tenant\Models\Tenant;

use function Pest\Laravel\assertDatabaseCount;
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
            'database' => [
                'host' => 'test',
                'port' => '3306 ',
                'name' => 'test',
                'username' => 'test',
                'password' => 'test',
            ],
            'domains' => [
                ['domain' => 'test.localhost'],
            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    assertDatabaseCount(Tenant::class, 1);
});
