<?php

declare(strict_types=1);

namespace Domain\Currency\Actions;

use Domain\Currency\DataTransferObjects\CurrencyData;
use Domain\Currency\Models\Currency;

class CreateCurrencyAction
{
    public function execute(CurrencyData $currencyData): Currency
    {
        return Currency::create([
            'code' => $currencyData->code,
            'name' => $currencyData->name,
            'enabled' => $currencyData->enabled,
            'exchange_rate' => $currencyData->exchange_rate,
            'default' => $currencyData->default,
        ]);
    }
}
