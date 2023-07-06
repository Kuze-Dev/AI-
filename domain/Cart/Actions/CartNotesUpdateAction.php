<?php

declare(strict_types=1);

namespace Domain\Cart\Actions;

use Domain\Cart\DataTransferObjects\CartNotesUpdateData;
use Domain\Cart\Models\CartLine;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CartNotesUpdateAction
{
    public function execute(CartNotesUpdateData $cartLineData)
    {
        $customerId = auth()->user()->id;

        $cartLine = CartLine::where('id', $cartLineData->cart_line_id)
            ->whereHas('cart', function ($query) use ($customerId) {
                $query->whereCustomerId($customerId);
            })
            ->whereNull('checked_out_at')->first();

        if (!$cartLine) {
            throw new ModelNotFoundException;
        }

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
