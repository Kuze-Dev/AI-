<?php

declare(strict_types=1);

namespace Domain\Tier\Actions;

use Domain\Tier\DataTransferObjects\TierData;
use Domain\Tier\Models\Tier;

class CreateTierAction
{
    public function execute(TierData $tierData): Tier
    {
        return Tier::create([
            'name' => $tierData->name,
            'description' => $tierData->description,
            'has_approval' => $tierData->has_approval,
        ]);
    }
}
