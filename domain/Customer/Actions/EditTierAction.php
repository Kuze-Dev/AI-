<?php

declare(strict_types=1);

namespace Domain\Customer\Actions;

use Domain\Customer\DataTransferObjects\TierData;
use Domain\Customer\Models\Tier;

class EditTierAction
{
    public function execute(Tier $tier, TierData $tierData): Tier
    {
        $tier->update([
            'name' => $tierData->name,
            'description' => $tierData->description,
        ]);

        return $tier;
    }
}
