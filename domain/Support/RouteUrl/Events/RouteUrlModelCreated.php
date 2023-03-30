<?php

declare(strict_types=1);

namespace Domain\Support\RouteUrl\Events;

use Domain\Support\RouteUrl\Contracts\HasRouteUrl;
use Illuminate\Database\Eloquent\Model;

class RouteUrlModelCreated
{
    public function __construct(
        public Model&HasRouteUrl $model
    ) {
    }
}
