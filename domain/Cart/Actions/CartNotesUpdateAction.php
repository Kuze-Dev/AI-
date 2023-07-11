<?php

declare(strict_types=1);

namespace Domain\Cart\Actions;

use Domain\Cart\DataTransferObjects\CartNotesUpdateData;
use Domain\Cart\Models\CartLine;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\UploadedFile;

class CartNotesUpdateAction
{
    public function execute(CartNotesUpdateData $cartLineData): CartLine
    {
        $checkCart = CartLine::whereHas('cart', function ($query) {
            $query->whereBelongsTo(auth()->user());
        })
            ->where('id', $cartLineData->cart_line_id)
            ->whereNull('checked_out_at')->first();

        if (!$checkCart) {
            throw new ModelNotFoundException();
        }

        $cartLine = CartLine::find($cartLineData->cart_line_id);

        if (!$cartLine) {
            throw new ModelNotFoundException();
        }

        if (!is_null($cartLineData->files)) {
            foreach ($cartLineData->files as $file) {
                $uploadedFile = new UploadedFile(
                    $file->getRealPath(),
                    $file->getClientOriginalName(),
                    $file->getClientMimeType(),
                    null,
                    true
                );

                $cartLine->addMedia($uploadedFile)->toMediaCollection('cart_line_notes');
            }
        }

        $cartLine->update([
            'meta' => $cartLineData->meta,
        ]);

        return $cartLine;
    }
}
