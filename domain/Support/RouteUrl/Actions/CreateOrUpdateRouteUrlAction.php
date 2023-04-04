<?php

declare(strict_types=1);

namespace Domain\Support\RouteUrl\Actions;

use Domain\Support\RouteUrl\Contracts\HasRouteUrl;
use Domain\Support\RouteUrl\DataTransferObjects\RouteUrlData;
use Domain\Support\RouteUrl\Models\RouteUrl;
use Domain\Support\RouteUrl\Support;
use Illuminate\Database\Eloquent\Model;

class CreateOrUpdateRouteUrlAction
{
    public function execute(Model&HasRouteUrl $model, RouteUrlData $routeUrlData): void
    {
        $newUrl = $routeUrlData->url ?? $model->getRouteUrlDefaultUrl();

        if (
            Support::activeQueryBuilder()
                ->whereUrl($newUrl)
                ->where('model_type', '!=', $model->getMorphClass())
                ->where('model_id', '!=', $model->getKey())
                ->exists()
        ) {
            abort(422, "Then [$newUrl] is already been used.");
        }

        $activeRouteUrl = RouteUrl::whereUrl($newUrl)
            ->first();

        $activeRouteUrl?->delete();

        $model->routeUrls()
            ->create([
                'url' => $newUrl,
                'is_override' => filled($routeUrlData->url),
            ]);
    }
}
