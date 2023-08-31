<?php

declare(strict_types=1);

namespace Support\RouteUrl\Actions;

use App\Features\CMS\Internationalization;
use Domain\Internationalization\Models\Locale;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Support\RouteUrl\Contracts\HasRouteUrl;
use Support\RouteUrl\DataTransferObjects\RouteUrlData;
use Support\RouteUrl\Models\RouteUrl;

class CreateOrUpdateRouteUrlAction
{
    public function execute(Model&HasRouteUrl $model, RouteUrlData $routeUrlData, string $locale = null): RouteUrl
    {

        $url = tenancy()->tenant?->features()->active(Internationalization::class) && Locale::where('is_default', true)->first()?->code !== $locale
            ? $routeUrlData->url
            : ($routeUrlData->is_override ? $routeUrlData->url : $model::generateRouteUrl($model, $model->getAttributes()));

        $url = Str::of($url)->trim('/')->prepend('/');

        /** @var ?RouteUrl $routeUrl */
        $routeUrl = RouteUrl::whereUrl($url)
            ->where('model_id', $model->id)
            ->first();

        if (!$routeUrl) {
            return $model->routeUrls()
                ->create([
                    'url' => $url,
                    'is_override' => $routeUrlData->is_override,
                ]);
        }
        if ($model->activeRouteUrl()->is($routeUrl)) {
            $routeUrl->model()
                ->associate($model)
                ->fill(['is_override' => $routeUrlData->is_override])
                ->touch();

            return $routeUrl;
        }

        $routeUrl->model()
            ->associate($model)
            ->fill(['is_override' => $routeUrlData->is_override])
            ->touch();

        return $routeUrl;
    }
}
