<?php

declare(strict_types=1);

namespace Domain\Cart\Actions;

use Domain\Cart\Models\Cart;
use Illuminate\Database\Eloquent\Builder;

class CartDestroyAction
{
    public function execute(Cart $cart): bool
    {
        $cart->whereBelongsTo(auth()->user())->first();

        return $cart->delete();
    }
}
