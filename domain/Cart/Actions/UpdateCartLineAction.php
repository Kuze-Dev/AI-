<?php

declare(strict_types=1);

namespace Domain\Cart\Actions;

use Domain\Cart\DataTransferObjects\UpdateCartLineData;
use Domain\Cart\Models\CartLine;
use Domain\Media\Actions\CreateMediaFromS3UrlAction;

class UpdateCartLineAction
{
    public function __construct(
        private readonly CreateMediaFromS3UrlAction $createMediaFromS3UrlAction
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
                'remarks' => $cartLineData->remarks->notes !== null ? [
                    'notes' => $cartLineData->remarks->notes,
                ] : null,
            ]);

            if ($cartLineData->remarks->medias && count($cartLineData->remarks->medias) > 0) {
                $this->createMediaFromS3UrlAction->execute(
                    $cartLine,
                    $cartLineData->remarks->medias,
                    'cart_line_notes'
                );
            } else {
                $cartLine->clearMediaCollection('cart_line_notes');
            }
        }

        return $cartLine;
    }
}
