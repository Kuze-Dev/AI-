<?php

declare(strict_types=1);

namespace Domain\Support\RouteUrl\Actions;

use Domain\Support\RouteUrl\Contracts\HasRouteUrl;
use Domain\Support\RouteUrl\Models\RouteUrl;
use Illuminate\Database\Eloquent\Model;

class CreateOrUpdateRouteUrlAction
{
    public function execute(Model&HasRouteUrl $model, string $url = null): void
    {
        $is_overriden = $url !== null;
        $url ??= $model::generateRouteUrl($model, $model->getAttributes());

        /** @var ?RouteUrl $routeUrl */
        $routeUrl = RouteUrl::whereUrl($url)
            ->first();

        if ($routeUrl !== null) {
            $routeUrl->model()
                ->associate($model)
                ->save();
        } else {
            $model->routeUrls()
                ->create([
                    'url' => $url,
                    'is_override' => $is_overriden,
                ]);
        }
    }
}
