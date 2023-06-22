<?php

declare(strict_types=1);

namespace Domain\Customer\Actions;

use Domain\Customer\Models\Tier;

class ForceDeleteTierAction
{
    public function execute(Tier $tier): ?bool
    {
        return $tier->forceDelete();
    }
}
