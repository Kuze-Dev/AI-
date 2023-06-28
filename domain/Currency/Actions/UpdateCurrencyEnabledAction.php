<?php

declare(strict_types=1);

namespace Domain\Currency\Actions;

use Domain\Currency\Models\Currency;

class UpdateCurrencyEnabledAction
{
    public function execute(Currency $currency): Currency
    {
        if ($currency->enabled) {
            return $currency;
        }

        Currency::where('id', '!=', $currency->id)->update(['enabled' => false]);
        $currency->update(['enabled' => true]);
        // dd($currency->enabled);

        return $currency;
    }
}
