<?php

declare(strict_types=1);

namespace Support\RouteUrl\Actions;

use App\Features\CMS\Internationalization;
use Domain\Tenant\TenantFeatureSupport;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Support\RouteUrl\Contracts\HasRouteUrl;
use Support\RouteUrl\DataTransferObjects\RouteUrlData;
use Support\RouteUrl\Models\RouteUrl;

class CreateOrUpdateRouteUrlAction
{
    public function execute(Model&HasRouteUrl $model, RouteUrlData $routeUrlData): RouteUrl
    {

        $url = $routeUrlData->is_override || TenantFeatureSupport::active(Internationalization::class) ?
            $routeUrlData->url :
            $model::generateRouteUrl($model, $model->getAttributes());

        $url = Str::of($url)->trim('/')->prepend('/');

        /** @var ?RouteUrl $routeUrl */
        $routeUrl = RouteUrl::whereModelType($model->getMorphClass())
            ->where('model_id', $model->getKey())
            ->first();

        if (! $routeUrl) {

            return $model->routeUrls()
                ->create([
                    'url' => $url,
                    'is_override' => $routeUrlData->is_override,
                ]);
        } else {

            $routeUrl->update([
                'url' => $url,
                'is_override' => $routeUrlData->is_override,
            ]);
        }

        return $routeUrl;
    }
}
