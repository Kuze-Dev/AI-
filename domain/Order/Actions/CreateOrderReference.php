<?php

declare(strict_types=1);

namespace Domain\Order\Actions;

use Domain\Order\Models\Order;
use Illuminate\Support\Str;

class CreateOrderReference
{
    public function execute(): string
    {
        $uniqueReference = Str::upper(Str::random(12));

        do {
            $existingReference = Order::where('reference', $uniqueReference)->first();

            if (!$existingReference) {
                $uniqueReference = $uniqueReference;
                break;
            }
        } while (true);

        return $uniqueReference;
    }
}
