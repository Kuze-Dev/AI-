<?php

declare(strict_types=1);

namespace Tests\Feature\FilamentTenant\Resources\ServiceResource;

use App\Features\Service\ServiceBase;
use App\FilamentTenant\Resources\ServiceResource\Pages\ListServices;
use Database\Seeders\Tenant\Auth\PermissionSeeder;
use Database\Seeders\Tenant\Auth\RoleSeeder;
use Domain\Currency\Database\Factories\CurrencyFactory;
use Domain\Service\Databases\Factories\ServiceFactory;
use Domain\Tenant\Database\Factories\TenantFactory;
use Filament\Facades\Filament;
use Filament\Pages\Actions\DeleteAction;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\URL;
use Support\MetaData\Database\Factories\MetaDataFactory;

use function Pest\Laravel\seed;
use function Pest\Livewire\livewire;

beforeEach(function () {
    $tenant = TenantFactory::new()->createOne(['name' => 'testing']);

    $domain = 'test.' . parse_url(config('app.url'), PHP_URL_HOST);

    $tenant->createDomain(['domain' => $domain]);

    $tenant->features()->activate(ServiceBase::class);

    URL::forceRootUrl(Request::getScheme() . '://' . $domain);

    tenancy()->initialize($tenant);

    seed([
        PermissionSeeder::class,
        RoleSeeder::class,
    ]);

    Filament::setContext('filament-tenant');
    loginAsSuperAdmin();
    CurrencyFactory::new()->createOne([
        'enabled' => true,
    ]);
});

it('can render page', function () {
    livewire(ListServices::class)
        ->assertOk();
});

it('can list services', function () {
    $services = ServiceFactory::new()
        ->withDummyBlueprint()
        ->count(10)
        ->create();

    livewire(ListServices::class)
        ->assertCanSeeTableRecords($services)
        ->assertOk();
});

it('can delete services', function () {
    $service = ServiceFactory::new()
        ->withDummyBlueprint()
        ->has(MetaDataFactory::new())
        ->createOne();

    livewire(ListServices::class)
        ->callTableAction(DeleteAction::class, $service)
        ->assertOk();

});
