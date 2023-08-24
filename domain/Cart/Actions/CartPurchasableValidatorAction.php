<?php

declare(strict_types=1);

namespace Domain\Cart\Actions;

use Domain\Cart\Exceptions\InvalidPurchasableException;
use Domain\Cart\Models\CartLine;
use Domain\Product\Models\Product;
use Domain\Product\Models\ProductVariant;

class CartPurchasableValidatorAction
{
    public function validateProduct(string $productId, int $quantity)
    {
        $product = Product::where((new Product())->getRouteKeyName(), $productId)->first();

        if ( ! $product) {
            throw new InvalidPurchasableException('Invalid product.');
        }

        if ( ! $product->status) {
            throw new InvalidPurchasableException('Inactive product.');
        }

        if ( ! $product->allow_stocks) {
            return;
        }

        $cartLine = CartLine::whereHas('cart', function ($query) {
            $query->whereBelongsTo(auth()->user());
        })
            ->whereNull('checked_out_at')
            ->where('purchasable_type', Product::class)
            ->where('purchasable_id', $product->id)->first();

        if ($quantity > $product->stock) {
            throw new InvalidPurchasableException('The quantity exceeds the available quantity of the product.');
        }

        if ($cartLine) {
            $payloadQuantity = $cartLine->quantity + $quantity;
            if ($payloadQuantity > $product->stock) {
                throw new InvalidPurchasableException($quantity . ' can not be added to cart. The quantity is limited to ' . $product->stock);
            }
        }
    }

    public function validateProductVariant(
        string $productId,
        string|int $variantId,
        int $quantity
    ) {
        $productVariant = ProductVariant::with('product')->where(
            (new ProductVariant())->getRouteKeyName(),
            $variantId
        )->whereHas('product', function ($query) use ($productId) {
            $query->where((new Product())->getRouteKeyName(), $productId);
        })->first();

        if ( ! $productVariant || ! $productVariant->product->status) {
            throw new InvalidPurchasableException('Invalid productVariant.');
        }

        if ( ! $productVariant->product->status) {
            throw new InvalidPurchasableException('Inactive productVariant.');
        }

        if ( ! $productVariant->product->allow_stocks) {
            return;
        }

        $cartLine = CartLine::whereHas('cart', function ($query) {
            $query->whereBelongsTo(auth()->user());
        })
            ->whereNull('checked_out_at')
            ->where('purchasable_type', ProductVariant::class)
            ->where('purchasable_id', $productVariant->id)->first();

        if ($quantity > $productVariant->stock) {
            throw new InvalidPurchasableException('The quantity exceeds the available quantity of the product.');
        }

        if ($cartLine) {
            $payloadQuantity = $cartLine->quantity + $quantity;
            if ($payloadQuantity > $productVariant->stock) {
                throw new InvalidPurchasableException($quantity .
                    ' can not be added to cart. The quantity is limited to ' . $productVariant->stock);
            }
        }
    }

    public function validatePurchasableUpdate(Product|ProductVariant $purchasable, int $quantity)
    {
        if ($purchasable instanceof Product) {
            if ( ! $purchasable->status) {
                throw new InvalidPurchasableException('Inactive product.');
            }

            if ( ! $purchasable->allow_stocks) {
                return;
            }
        } elseif ($purchasable instanceof ProductVariant) {
            if ( ! $purchasable->product->status) {
                throw new InvalidPurchasableException('Inactive productVariant.');
            }

            if ( ! $purchasable->product->allow_stocks) {
                return;
            }
        }

        if ($quantity > $purchasable->stock) {
            throw new InvalidPurchasableException('Quantity exceeds stock');
        }
    }

    public function validateCheckout(array $cartLineIds): int
    {
        $cartLines = CartLine::with('purchasable')
            ->whereHas('cart', function ($query) {
                $query->whereBelongsTo(auth()->user());
            })
            ->whereNull('checked_out_at')
            ->whereIn((new CartLine())->getRouteKeyName(), $cartLineIds)->get();

        $count = 0;

        foreach ($cartLines as $cartLine) {
            if ($cartLine->purchasable instanceof Product) {
                if ( ! $cartLine->purchasable->status) {
                    throw new InvalidPurchasableException('Inactive product.');
                }
                if ( ! $cartLine->purchasable->allow_stocks) {
                    $count++;
                } else {
                    if ($cartLine->purchasable->stock >= $cartLine->quantity) {
                        $count++;
                    }
                }
            } elseif ($cartLine->purchasable instanceof ProductVariant) {
                if ( ! $cartLine->purchasable->status) {
                    throw new InvalidPurchasableException('Inactive productVariant.');
                }
                if (
                    ! $cartLine->purchasable->product->allow_stocks
                ) {
                    $count++;
                } else {
                    if ($cartLine->purchasable->stock >= $cartLine->quantity) {
                        $count++;
                    }
                }
            }
        }

        return $count;
    }

    public function validateAuth(array $cartLineIds): int
    {
        return CartLine::with('purchasable')
            ->whereIn((new CartLine())->getRouteKeyName(), $cartLineIds)
            ->whereHas('cart', function ($query) {
                $query->whereBelongsTo(auth()->user());
            })
            ->whereNull('checked_out_at')
            ->count();
    }
}
