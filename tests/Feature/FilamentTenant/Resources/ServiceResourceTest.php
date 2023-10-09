<?php

declare(strict_types=1);

use App\Features\Service\ServiceBase;
use App\FilamentTenant\Resources\ServiceResource;
use Database\Seeders\Tenant\Auth\PermissionSeeder;
use Database\Seeders\Tenant\Auth\RoleSeeder;
use Domain\Service\Databases\Factories\ServiceFactory;
use Domain\Tenant\Database\Factories\TenantFactory;
use Filament\Facades\Filament;

use function Pest\Laravel\seed;

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
});

it('can globally search', function () {

    $service = ServiceFactory::new()
        ->withDummyBlueprint()
        ->createOne();

    $results = Filament::getGlobalSearchProvider()
        ->getResults($service->name);

    expect($results->getCategories()['services']->first()->url)
        ->toEqual(ServiceResource::getUrl('edit', [$service]));
});
