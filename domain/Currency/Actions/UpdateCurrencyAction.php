<?php

declare(strict_types=1);

namespace Domain\Currency\Actions;

use Domain\Currency\Models\Currency;

class UpdateCurrencyAction
{
    public function execute(Currency $currency): Currency
    {
       
        if (!$currency->enabled) {
            Currency::where('id', '!=', $currency->id)->update(['enabled' => false]);
            Currency::where('id', $currency->id)->update(['enabled' => true]);
            // dd($currency->enabled);
        }

        return $currency;
    }
}
