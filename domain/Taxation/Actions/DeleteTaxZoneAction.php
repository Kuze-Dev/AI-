<?php

declare(strict_types=1);

namespace Domain\Taxation\Actions;

use Domain\Taxation\Models\TaxZone;

class DeleteTaxZoneAction
{
    public function execute(TaxZone $taxZone): ?bool
    {
        return $taxZone->delete();
    }
}
