<?php

declare(strict_types=1);

namespace Domain\Cart\Listeners;

use Domain\Cart\Actions\BulkDestroyCartLineAction;
use Domain\Cart\Events\SanitizeCartEvent;

class SanitizeCartListener
{
    /**
     * Handle the event.
     *
     * @param  \Domain\Cart\Events\SanitizeCartEvent  $event
     * @return void
     */
    public function handle(SanitizeCartEvent $event): void
    {
        app(BulkDestroyCartLineAction::class)
            ->execute($event->cartLineIds);
    }
}
