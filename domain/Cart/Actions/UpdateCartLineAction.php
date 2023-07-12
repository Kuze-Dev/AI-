<?php

declare(strict_types=1);

namespace Domain\Cart\Actions;

use Domain\Cart\DataTransferObjects\UpdateCartLineData;
use Domain\Cart\Models\CartLine;
use Exception;

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

        $cartLine->clearMediaCollection('cart_line_notes');

        if ($cartLineData->medias !== null) {
            foreach ($cartLineData->medias as $imageUrl) {
                try {
                    $cartLine->addMediaFromUrl($imageUrl)
                        ->toMediaCollection('cart_line_notes');
                } catch (Exception $e) {
                    // Log::info($e);
                }
            }
        }

        return $cartLine;
    }
}
