<?php

declare(strict_types=1);

namespace Domain\Cart\Actions;

use Domain\Cart\DataTransferObjects\UpdateCartLineData;
use Domain\Cart\Models\CartLine;

class UpdateCartLineAction
{
    public function execute(CartLine $cartLine, UpdateCartLineData $cartLineData): CartLine
    {
        if ($cartLineData->quantity) {
            $cartLine->update([
                'quantity' => $cartLineData->quantity,
            ]);
        }

        if ($cartLineData->remarks) {
            $cartLine->update([
                'remarks' => $cartLineData->remarks,
            ]);
        }

        if ($cartLineData->images !== null) {
            foreach ($cartLineData->images as $image) {
                $cartLine->addMedia($image)
                    ->toMediaCollection('cart_line_notes');
            }
        }

        return $cartLine;
    }
}
