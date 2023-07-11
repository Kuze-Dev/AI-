<?php

declare(strict_types=1);

namespace Domain\Cart\Actions;

use Domain\Cart\Models\Cart;

class DestroyCartAction
{
    public function execute(Cart $cart): bool
    {
        return $cart->delete();
    }
}
