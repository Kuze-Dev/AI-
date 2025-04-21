<?php

declare(strict_types=1);

use Domain\Currency\Actions\UpdateCurrencyEnabledAction;
use Domain\Currency\Database\Factories\CurrencyFactory;
use Domain\Currency\Models\Currency;

use function Pest\Laravel\assertDatabaseHas;

beforeEach(function () {
    testInTenantContext();
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
