<?php

declare(strict_types=1);

namespace Domain\Tier\Actions;

use Domain\Tier\DataTransferObjects\TierData;
use Domain\Tier\Models\Tier;

class EditTierAction
{
    public function execute(Tier $tier, TierData $tierData): Tier
    {
        $tier->update([
            'name' => $tierData->name,
            'description' => $tierData->description,
            'has_approval' => $tierData->has_approval,
        ]);

        return $tier;
    }
}
