<?php

declare(strict_types=1);

namespace Domain\Cart\Actions\PublicCart;

use Domain\Cart\Models\Cart;
use Illuminate\Support\Str;

class GuestCreateCartAction
{
    public function execute(?string $sessionId): Cart
    {
        if ($sessionId) {
            $cart = Cart::where([
                'session_id' => $sessionId,
            ])->first();

            if ($cart) {
                return $cart;
            }
        }

        $generatedId = $this->generateUniqueSessionId();

        $newCart = Cart::firstOrCreate([
            'uuid' => (string) Str::uuid(),
            'session_id' => $generatedId,
        ]);

        return $newCart;
    }

    private function generateUniqueSessionId(): string
    {
        $uuid = uuid_create(UUID_TYPE_RANDOM);

        $sessionId = time().$uuid;

        return $sessionId;
    }
}
