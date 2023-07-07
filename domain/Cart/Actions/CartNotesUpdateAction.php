<?php

declare(strict_types=1);

namespace Domain\Cart\Actions;

use Domain\Cart\DataTransferObjects\CartNotesUpdateData;
use Domain\Cart\Models\CartLine;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\UploadedFile;

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
