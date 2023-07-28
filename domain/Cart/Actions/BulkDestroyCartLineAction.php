<?php

declare(strict_types=1);

namespace Domain\Cart\Actions;

use DB;
use Domain\Cart\Models\CartLine;
use Exception;

class BulkDestroyCartLineAction
{
    public function execute(array $cartLineIds): bool
    {
        try {
            DB::beginTransaction();

            $cartLines = CartLine::whereIn((new CartLine())->getRouteKeyName(), $cartLineIds);

            $cartLines->delete();

            return true;
        } catch (Exception) {
            DB::rollBack();
            return false;
        }
    }
}
