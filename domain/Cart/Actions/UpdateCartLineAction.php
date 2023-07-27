<?php

declare(strict_types=1);

namespace Domain\Cart\Actions;

use Domain\Cart\DataTransferObjects\UpdateCartLineData;
use Domain\Cart\Models\CartLine;
use Domain\Media\Actions\CreateMediaFromUrlAction;

class UpdateCartLineAction
{
    public function __construct(
        private readonly CreateMediaFromUrlAction $createMediaFromUrlAction
    ) {
    }

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

        if ($cartLineData->medias !== null) {
            $this->createMediaFromUrlAction->execute($cartLine, $cartLineData->medias, 'cart_line_notes');
        }

        return $cartLine;
    }
}
