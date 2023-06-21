<?php

declare(strict_types=1);

namespace Domain\Currency\Actions;

use Domain\Currency\DataTransferObjects\CurrencyData;
use Domain\Currency\Models\Currency;

class UpdateCurrencyAction
{
    public function execute(Currency $currency, CurrencyData $currencyData): Currency
    {
   

        $currency->update([
            'code' => $currencyData->code,
            'name' => $currencyData->name,
            'enabled' => $currencyData->enabled,
            'exchange_rate' => $currencyData->exchange_rate,
            'default' => $currencyData->default,
        ]);

        return $currency;
    }


}
