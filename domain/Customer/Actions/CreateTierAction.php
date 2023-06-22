<?php

declare(strict_types=1);

namespace Domain\Customer\Actions;

use Domain\Customer\DataTransferObjects\TierData;
use Domain\Customer\Models\Tier;

class CreateTierAction
{
    public function execute(TierData $tierData): Tier
    {
        return Tier::create([
            'name' => $tierData->name,
            'description' => $tierData->description,
        ]);
    }
}
