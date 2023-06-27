<?php

declare(strict_types=1);

namespace Domain\Currency\Actions;

use Domain\Currency\Models\Currency;

class UpdateCurrencyAction
{
    public function execute(Currency $currency): void
    {

        if ($currency->enabled) {
            Currency::where('id', '!=', $currency->id)->update(['enabled' => false]);
        }

        if ($currency->default) {
            Currency::where('id', '!=', $currency->id)->update(['default' => false]);
        }

        if ( ! Currency::where('enabled', true)->exists()) {
            Currency::where('default', true)->update(['enabled' => true]);
        }

    }
}
