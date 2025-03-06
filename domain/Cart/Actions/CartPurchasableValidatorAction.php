<?php

declare(strict_types=1);

namespace Domain\Cart\Actions;

use Domain\Cart\Enums\CartUserType;
use Domain\Cart\Exceptions\InvalidPurchasableException;
use Domain\Cart\Models\CartLine;
use Domain\Product\Models\Product;
use Domain\Product\Models\ProductVariant;

class CartPurchasableValidatorAction
{
    public function validateProduct(
        string $productId,
        int $quantity,
        int|string $userId,
        CartUserType $type
    ): void {
        $product = Product::where((new Product)->getRouteKeyName(), $productId)->firstOrFail();

        $this->validatePurchasable($product);

        $cartLine = CartLine::whereHas('cart', function ($query) use ($userId, $type) {
            if ($type === CartUserType::AUTHENTICATED) {
                $query->where('customer_id', $userId);
            } elseif ($type === CartUserType::GUEST) {
                $query->where('session_id', $userId);
            }
        })
            ->whereNull('checked_out_at')
            ->where('purchasable_type', Product::class)
            ->where('purchasable_id', $product->id)->first();

        // $this->validateMinimumQuantity($product, $quantity, $cartLine);

        if ($type === CartUserType::GUEST) {
            // purchasable by guest
            $this->validatePurchasableByGuest($product);
        }

        // stock control
        if (! $product->allow_stocks) {
            return;
        }

        $this->validateStockControl($product, $quantity, $cartLine);
    }

    public function validateProductVariant(
        string $productId,
        string|int $variantId,
        int $quantity,
        int|string $userId,
        CartUserType $type
    ): void {
        $productVariant = ProductVariant::with('product')->where(
            (new ProductVariant)->getRouteKeyName(),
            $variantId
        )->whereHas('product', function ($query) use ($productId) {
            $query->where((new Product)->getRouteKeyName(), $productId);
        })->firstOrFail();

        $this->validatePurchasable($productVariant);

        $cartLine = CartLine::whereHas('cart', function ($query) use ($userId, $type) {
            if ($type === CartUserType::AUTHENTICATED) {
                $query->where('customer_id', $userId);
            } elseif ($type === CartUserType::GUEST) {
                $query->where('session_id', $userId);
            }
        })
            ->whereNull('checked_out_at')
            ->where('purchasable_type', ProductVariant::class)
            ->where('purchasable_id', $productVariant->id)->first();

        /** @var \Domain\Product\Models\Product $product */
        $product = $productVariant->product;

        // $this->validateMinimumQuantity($product, $quantity, $cartLine);

        if ($type === CartUserType::GUEST) {
            // purchasable by guest
            $this->validatePurchasableByGuest($product);
        }

        // stock control
        if (! $product->allow_stocks) {
            return;
        }
        $this->validateStockControl($productVariant, $quantity, $cartLine);
    }

    public function validatePurchasableUpdate(Product|ProductVariant $purchasable, int $quantity): void
    {
        if ($purchasable instanceof Product) {
            $this->validatePurchasable($purchasable);
            // $this->validateMinimumQuantity($purchasable, $quantity);

            if (! $purchasable->allow_stocks) {
                return;
            }
        } elseif ($purchasable instanceof ProductVariant) {
            /** @var \Domain\Product\Models\Product $product */
            $product = $purchasable->product;

            $this->validatePurchasable($product);
            // $this->validateMinimumQuantity($product, $quantity);

            if (! $product->allow_stocks) {
                return;
            }
        }

        $this->validateStockControl($purchasable, $quantity);
    }

    public function validateCheckout(array $cartLineIds, int|string $userId, CartUserType $type): int
    {
        $cartLines = CartLine::with('purchasable')
            ->whereHas('cart', function ($query) use ($userId, $type) {
                if ($type === CartUserType::AUTHENTICATED) {
                    $query->where('customer_id', $userId);
                } elseif ($type === CartUserType::GUEST) {
                    $query->where('session_id', $userId);
                }
            })
            ->whereNull('checked_out_at')
            ->whereIn((new CartLine)->getRouteKeyName(), $cartLineIds)->get();

        $count = 0;

        foreach ($cartLines as $cartLine) {
            if ($cartLine->purchasable instanceof Product) {
                /** @var \Domain\Product\Models\Product $product */
                $product = $cartLine->purchasable;

                $this->validatePurchasable($product);

                $this->validateMinimumQuantity($product, 0, $cartLine);

                if ($type === CartUserType::GUEST) {
                    // purchasable by guest
                    $this->validatePurchasableByGuest($product);
                }

                if (! $product->allow_stocks) {
                    $count++;
                } else {
                    if ($product->stock >= $cartLine->quantity) {
                        $count++;
                    }
                }
            } elseif ($cartLine->purchasable instanceof ProductVariant) {
                $cartLine->purchasable->load('product');

                /** @var \Domain\Product\Models\Product $product */
                $product = $cartLine->purchasable->product;

                /** @var \Domain\Product\Models\ProductVariant $productVariant */
                $productVariant = $cartLine->purchasable;

                $this->validatePurchasable($product);

                $this->validateMinimumQuantity($product, 0, $cartLine);

                if ($type === CartUserType::GUEST) {
                    // purchasable by guest
                    $this->validatePurchasableByGuest($product);
                }

                if (
                    ! $product->allow_stocks
                ) {
                    $count++;
                } else {
                    if ($productVariant->stock >= $cartLine->quantity) {
                        $count++;
                    }
                }
            }
        }

        return $count;
    }

    public function validatePurchasable(Product|ProductVariant $purchasable): void
    {
        if (! $purchasable->status) {
            throw new InvalidPurchasableException('Inactive purchasable.');
        }
    }

    public function validateMinimumQuantity(Product $product, int $quantity, ?CartLine $cartLine = null): void
    {
        // minimum order quantity
        if (! is_null($cartLine)) {
            $payloadQuantity = $cartLine->quantity + $quantity;
            if ($payloadQuantity < $product->minimum_order_quantity) {
                throw new InvalidPurchasableException('Minimum order quantity must be '.$product->minimum_order_quantity.'.');
            }
        } else {
            if ($quantity < $product->minimum_order_quantity) {
                throw new InvalidPurchasableException('Minimum order quantity must be '.$product->minimum_order_quantity.'.');
            }
        }
    }

    public function validateStockControl(Product|ProductVariant $purchasable, int $quantity, ?CartLine $cartLine = null): void
    {
        if ($quantity > $purchasable->stock) {
            throw new InvalidPurchasableException('The quantity exceeds the available quantity of the purchasable.');
        }

        if ($cartLine) {
            $payloadQuantity = $cartLine->quantity + $quantity;
            if ($payloadQuantity > $purchasable->stock) {
                throw new InvalidPurchasableException($quantity.' can not be added to cart. The quantity is limited to '.$purchasable->stock);
            }
        }
    }

    public function validateAuth(array $cartLineIds, int|string $userId, CartUserType $type): int
    {
        return CartLine::with('purchasable')
            ->whereIn((new CartLine)->getRouteKeyName(), $cartLineIds)
            ->whereHas('cart', function ($query) use ($userId, $type) {
                if ($type === CartUserType::AUTHENTICATED) {
                    $query->where('customer_id', $userId);
                } elseif ($type === CartUserType::GUEST) {
                    $query->where('session_id', $userId);
                }
            })
            ->whereNull('checked_out_at')
            ->count();
    }

    public function validatePurchasableByGuest(Product $product): void
    {
        if (! $product->allow_guest_purchase) {
            throw new InvalidPurchasableException("This product can't be purchased by guests.");
        }
    }
}
