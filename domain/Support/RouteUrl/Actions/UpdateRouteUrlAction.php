<?php

declare(strict_types=1);

namespace Domain\Support\RouteUrl\Actions;

use Domain\Support\RouteUrl\Contracts\HasRouteUrl;
use Domain\Support\RouteUrl\DataTransferObjects\RouteUrlData;
use Domain\Support\RouteUrl\Models\RouteUrl;
use Illuminate\Database\Eloquent\Model;

class UpdateRouteUrlAction
{
    public function __construct(protected CreateRouteUrlAction $createRouteUrl)
    {
    }

    public function execute(Model&HasRouteUrl $model, RouteUrlData $routeUrlData): Model&HasRouteUrl
    {
        $routeUrl = self::getRouteUrl($model);

        if (
            $routeUrl->is_override !== $routeUrlData->is_override ||
        $routeUrl->url !== $routeUrlData->url
        ) {
            $this->createRouteUrl->execute($model, $routeUrlData);
        }

        return $model;
    }

    private static function getRouteUrl(Model&HasRouteUrl $model): RouteUrl
    {
        return $model->routeUrls()->latest()->first();
    }
}
