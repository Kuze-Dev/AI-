<?php

declare(strict_types=1);

namespace Domain\Cart\Actions;

use Domain\Cart\Models\CartLine;
use Illuminate\Database\Eloquent\Builder;

class DestroyCartLineAction
{
    public function execute(CartLine $cartLine): bool
    {
        $cartLine->whereHas(
            'cart',
            function (Builder $query) {
                $query->whereBelongsTo(auth()->user());
            }
        )->whereNull('checked_out_at')->firstOrFail();

        return $cartLine->delete();
    }
}
