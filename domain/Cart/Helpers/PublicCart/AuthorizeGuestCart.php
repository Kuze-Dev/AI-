<?php

declare(strict_types=1);

namespace Domain\Cart\Helpers\PublicCart;

use Domain\Cart\Models\Cart;
use Domain\Cart\Models\CartLine;
use Illuminate\Database\Eloquent\Model;

class AuthorizeGuestCart
{
    public function execute(Model $model, ?string $sessionId): bool
    {
        if (is_null($sessionId)) {
            return false;
        }

        if ($model instanceof CartLine) {
            /** @var \Domain\Cart\Models\Cart $cart */
            $cart = $model->cart;

            return $cart->session_id === $sessionId;
        }

        if ($model instanceof Cart) {
            return $model->session_id === $sessionId;
        }

        return false;
    }
}
