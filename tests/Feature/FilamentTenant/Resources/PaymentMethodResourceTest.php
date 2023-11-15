<?php

declare(strict_types=1);

use App\Features\Shopconfiguration\PaymentGateway\PaypalGateway;
use App\FilamentTenant\Resources\PaymentMethodResource;
use Domain\PaymentMethod\Database\Factories\PaymentMethodFactory;
use Filament\Facades\Filament;

beforeEach(function () {
    testInTenantContext();
    Filament::setContext('filament-tenant');
    loginAsSuperAdmin();

    tenancy()->tenant->features()->activate(PaypalGateway::class);
});

it('can globally search', function () {

    $record = PaymentMethodFactory::new()->createOne();

    $results = Filament::getGlobalSearchProvider()
        ->getResults($record->title);

    expect($results->getCategories()['payment methods']->first()->url)
        ->toEqual(PaymentMethodResource::getUrl('edit', [$record]));
});
