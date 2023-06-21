<?php

declare(strict_types=1);

namespace Domain\Currency\Actions;

use Domain\Currency\Models\Currency;

class DeleteCurrencyAction
{
    public function execute(Currency $currency): ?bool
    {
        return $currency->delete();
    }
}
