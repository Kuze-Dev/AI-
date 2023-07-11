<?php

declare(strict_types=1);

namespace Domain\Cart\Actions;

use Domain\Cart\Models\CartLine;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class BulkDestroyCartLineAction
{
    public function execute(array $cartLineIds): bool
    {
        $cartLines = CartLine::whereIn('id', $cartLineIds);

        $cartLines->delete();

        return true;
    }
}
