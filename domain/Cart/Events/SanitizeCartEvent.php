<?php

declare(strict_types=1);

namespace Domain\Cart\Events;

use Illuminate\Queue\SerializesModels;

class SanitizeCartEvent
{
    use SerializesModels;

    public array $cartLineIds;

    public function __construct(
        array $cartLineIds,
    ) {
        $this->cartLineIds = $cartLineIds;
    }
}
