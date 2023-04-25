<?php

declare(strict_types=1);

namespace Domain\Blueprint\Actions;

use Domain\Blueprint\Models\Blueprint;

class DeleteBlueprintAction
{
    public function execute(Blueprint $blueprint): ?bool
    {
        return $blueprint->delete();
    }
}
