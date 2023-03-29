<?php

declare(strict_types=1);

namespace Domain\Support\RouteUrl\Contracts;

use Illuminate\Database\Eloquent\Relations\MorphMany;

interface HasRouteUrl
{
    public function routeUrls(): MorphMany;
}
