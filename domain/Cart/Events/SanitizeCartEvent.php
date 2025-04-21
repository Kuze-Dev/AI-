<?php

declare(strict_types=1);

namespace Domain\Cart\Events;

use Illuminate\Queue\SerializesModels;

class SanitizeCartEvent
{
    use SerializesModels;

    public function __construct(public array $cartLineIds) {}
}
