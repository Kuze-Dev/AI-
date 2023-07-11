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
        'enabled' => false,
    ]);

    app(UpdateCurrencyEnabledAction::class)->execute($currency1);

    assertDatabaseHas(Currency::class, [
        'enabled' => true,
    ]);

});
