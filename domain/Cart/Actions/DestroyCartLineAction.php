<?php

declare(strict_types=1);

namespace Domain\Cart\Actions;

use Domain\Cart\Models\CartLine;

class DestroyCartLineAction
{
    public function execute(CartLine $cartLine): bool
    {
        $cartLine->whereNull('checked_out_at')->first();

        return (bool) $cartLine->delete();
    }
}
