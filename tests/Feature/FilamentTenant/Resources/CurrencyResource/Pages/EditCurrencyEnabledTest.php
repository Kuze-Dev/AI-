<?php

declare(strict_types=1);

use Domain\Currency\Database\Factories\CurrencyFactory;
use Domain\Currency\Actions\UpdateCurrencyEnabledAction;
use Domain\Currency\Models\Currency;
use Filament\Facades\Filament;

use function Pest\Laravel\assertDatabaseHas;

beforeEach(function () {
    testInTenantContext();
    Filament::setContext('filament-tenant');
    loginAsSuperAdmin();
});

it('can update currencies enabled', function () {
    $currency1 = CurrencyFactory::new()->createOne([
        'enabled' => true,
    ]);

    $currency2 = CurrencyFactory::new()->createOne([
        'enabled' => false,
    ]);

    app(UpdateCurrencyEnabledAction::class)->execute($currency2);

    assertDatabaseHas(Currency::class, [
        'id' => $currency2->id,
        'enabled' => true,
    ]);

    assertDatabaseHas(Currency::class, [

    ]);

});
