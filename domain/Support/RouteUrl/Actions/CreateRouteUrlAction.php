<?php

declare(strict_types=1);

namespace Domain\Support\RouteUrl\Actions;

use Domain\Support\RouteUrl\Contracts\HasRouteUrl;
use Domain\Support\RouteUrl\DataTransferObjects\RouteUrlData;
use Illuminate\Database\Eloquent\Model;

class CreateRouteUrlAction
{
    public function execute(Model&HasRouteUrl $model, RouteUrlData $routeUrlData): Model&HasRouteUrl
    {
        $model->routeUrls()
            ->create([
                'url' => $routeUrlData->url,
                'is_override' => $routeUrlData->is_override,
            ]);

        return $model;
    }
}
