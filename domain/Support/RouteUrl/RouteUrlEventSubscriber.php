<?php

declare(strict_types=1);

namespace Domain\Support\RouteUrl;

use Domain\Support\RouteUrl\Actions\CreateRouteUrlAction;
use Domain\Support\RouteUrl\Actions\UpdateRouteUrlAction;
use Domain\Support\RouteUrl\DataTransferObjects\RouteUrlData;
use Domain\Support\RouteUrl\Events\RouteUrlModelCreated;
use Domain\Support\RouteUrl\Events\RouteUrlModelUpdated;
use Illuminate\Events\Dispatcher;

class RouteUrlEventSubscriber
{
    public function __construct(
        private readonly CreateRouteUrlAction $createRouteUrl,
        private readonly UpdateRouteUrlAction $updateRouteUrl,
    ) {
    }

    public function subscribe(Dispatcher $dispatcher): void
    {
        $dispatcher->listen(
            RouteUrlModelCreated::class,
            function (RouteUrlModelCreated $event) {
                $this->createRouteUrl->execute(
                    $event->model,
                    new RouteUrlData(
                        $event->model->getRouteUrlUrl(),
                        $event->model->getRouteUrlIsOverride()
                    ),
                );
            }
        );
        $dispatcher->listen(
            RouteUrlModelUpdated::class,
            function (RouteUrlModelUpdated $event) {
                $this->updateRouteUrl->execute(
                    $event->model,
                    new RouteUrlData(
                        $event->model->getRouteUrlUrl(),
                        $event->model->getRouteUrlIsOverride()
                    )
                );
            }
        );
    }
}
