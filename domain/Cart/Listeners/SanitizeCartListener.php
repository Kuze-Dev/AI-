<?php

declare(strict_types=1);

namespace Domain\Cart\Listeners;

use Domain\Cart\Actions\BulkDestroyCartLineAction;
use Domain\Cart\Events\SanitizeCartEvent;

class SanitizeCartListener
{
    /**
     * Handle the event.
     */
    public function handle(SanitizeCartEvent $event): void
    {
        app(BulkDestroyCartLineAction::class)
            ->execute($event->cartLineIds);
    }
}
