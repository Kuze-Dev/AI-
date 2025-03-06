<?php

declare(strict_types=1);

namespace Domain\Cart\Actions;

use Domain\Cart\DataTransferObjects\CreateCartData;
use Domain\Cart\Models\Cart;
use Domain\Cart\Models\CartLine;
use Domain\Media\Actions\CreateMediaFromS3UrlAction;
use Domain\Product\Models\Product;
use Domain\Product\Models\ProductVariant;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

readonly class CreateCartLineAction
{
    public function __construct(
        private CreateMediaFromS3UrlAction $createMediaFromS3UrlAction
    ) {}

    public function execute(Cart $cart, CreateCartData $cartLineData): CartLine
    {
        $product = Product::where((new Product())->getRouteKeyName(), $cartLineData->purchasable_id)->first();

        if (! $product) {
            throw new BadRequestHttpException('Product not found');
        }

        $purchasableId = $product->id;

        $purchasableType = '';

        match ($cartLineData->purchasable_type) {
            'Product' => $purchasableType = Product::class,
            default => null
        };

        $productVariant = ProductVariant::find($cartLineData->variant_id);
        if ($productVariant) {
            $purchasableId = $productVariant->id;
            $purchasableType = ProductVariant::class;
        }

        $cartLine = CartLine::where([
            'cart_id' => $cart->id,
            'purchasable_id' => $purchasableId,
            'purchasable_type' => $purchasableType,
            'checked_out_at' => null,
        ])->first();

        if ($cartLine) {
            $cartLine->update([
                'quantity' => $cartLine->quantity + $cartLineData->quantity,
            ]);
        } else {
            $cartLine = CartLine::create([
                'uuid' => (string) Str::uuid(),
                'cart_id' => $cart->id,
                'purchasable_id' => $purchasableId,
                'purchasable_type' => $purchasableType,
                'quantity' => $cartLineData->quantity,
            ]);
        }

        if ($cartLineData->remarks) {
            $cartLine->update([
                'remarks' => $cartLineData->remarks->notes !== null ? [
                    'notes' => $cartLineData->remarks->notes,
                ] : null,
            ]);

            if ($cartLineData->remarks->medias) {
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
