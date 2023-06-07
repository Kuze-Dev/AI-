<?php

declare(strict_types=1);

namespace Domain\Support\RouteUrl\Actions;

use Domain\Support\RouteUrl\Contracts\HasRouteUrl;
use Domain\Support\RouteUrl\DataTransferObjects\RouteUrlData;
use Domain\Support\RouteUrl\Models\RouteUrl;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CreateOrUpdateRouteUrlAction
{
    public function execute(Model&HasRouteUrl $model, RouteUrlData $routeUrlData): RouteUrl
    {
        $url = $routeUrlData->is_override
            ? $routeUrlData->url
            : $model::generateRouteUrl($model, $model->getAttributes());

        $url = Str::of($url)->trim('/')->prepend('/');

        /** @var ?RouteUrl $routeUrl */
        $routeUrl = RouteUrl::whereUrl($url)
            ->first();

        if ( ! $routeUrl) {
            return $model->routeUrls()
                ->create([
                    'url' => $url,
                    'is_override' => $routeUrlData->is_override,
                ]);
        }

        if ($model->activeRouteUrl()->is($routeUrl)) {
            return $routeUrl;
        }

        $routeUrl->model()
            ->associate($model)
            ->fill(['is_override' => $routeUrlData->is_override])
            ->touch();

        return $routeUrl;
    }
}
