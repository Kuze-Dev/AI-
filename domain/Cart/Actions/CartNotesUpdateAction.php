<?php

declare(strict_types=1);

namespace Domain\Cart\Actions;

use Domain\Cart\DataTransferObjects\CartNotesUpdateData;
use Domain\Cart\Models\CartLine;

class CartNotesUpdateAction
{
    public function execute(CartNotesUpdateData $cartLineData)
    {
        $cartLine = CartLine::find($cartLineData->cart_line_id);

        if (!is_null($cartLineData->file)) {
            $cartLine->addMedia($cartLineData->file)->toMediaCollection('cart_line_notes');
        }

        $cartLine->update([
            'notes' => $cartLineData->notes,
        ]);

        return $cartLine;
    }
}
