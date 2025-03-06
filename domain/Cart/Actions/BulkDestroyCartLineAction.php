<?php

declare(strict_types=1);

namespace Domain\Cart\Actions;

use Domain\Cart\Models\CartLine;

class BulkDestroyCartLineAction
{
    public function execute(array $cartLineIds): bool
    {
        $cartLines = CartLine::whereIn((new CartLine)->getRouteKeyName(), $cartLineIds);

        return (bool) $cartLines->delete();
    }
}
