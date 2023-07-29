<?php

declare(strict_types=1);

namespace Domain\Cart\Actions;

use Domain\Cart\Models\CartLine;
use Illuminate\Database\Eloquent\Builder;

class DestroyCartLineAction
{
    public function execute(CartLine $cartLine): bool
    {
        /** @var \Domain\Customer\Models\Customer $customer */
        $customer = auth()->user();

        $cartLine->whereHas('cart', function (Builder $query) use ($customer) {
            $query->whereBelongsTo($customer);
        })->whereNull('checked_out_at')->first();

        return (bool) $cartLine->delete();
    }
}
