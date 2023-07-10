<?php

declare(strict_types=1);

namespace Domain\Cart\Actions;

use Domain\Cart\Models\CartLine;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CartLineBulkDestroyAction
{
    public function execute(array $cartLineIds): bool
    {
        $cartLines = CartLine::query()
            ->whereHas('cart', function (Builder $query) {
                $query->whereBelongsTo(auth()->user());
            })
            ->whereIn('id', $cartLineIds)
            ->whereNull('checked_out_at');

        if (count($cartLineIds) !== $cartLines->count()) {
            throw new ModelNotFoundException('Cart lines not found');
        }

        $cartLines->get()->each->delete();

        return true;
    }
}
