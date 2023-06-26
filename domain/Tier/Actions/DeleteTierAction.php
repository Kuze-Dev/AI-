<?php

declare(strict_types=1);

namespace Domain\Tier\Actions;

use Domain\Tier\Models\Tier;

class DeleteTierAction
{
    public function execute(Tier $tier): ?bool
    {
        return $tier->delete();
    }
}
