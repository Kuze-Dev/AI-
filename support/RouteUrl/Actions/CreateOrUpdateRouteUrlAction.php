<?php

declare(strict_types=1);

namespace Support\RouteUrl\Actions;

use Support\RouteUrl\Contracts\HasRouteUrl;
use Support\RouteUrl\DataTransferObjects\RouteUrlData;
use Support\RouteUrl\Models\RouteUrl;
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
        $routeUrl = RouteUrl::whereModelType($model->getMorphClass())
            ->where('model_id', $model->id)
            ->first();
       
        if ( ! $routeUrl) {

            return $model->routeUrls()
                ->create([
                    'url' => $url,
                    'is_override' => $routeUrlData->is_override,
                ]);
        }else {
            
            $routeUrl->update([
                'url' => $url,
                'is_override' => $routeUrlData->is_override,
            ]);
        }

        // if ($model->activeRouteUrl()->is($routeUrl)) {
        //     return $routeUrl;
        // }

        // $routeUrl->model()
        //     ->associate($model)
        //     ->fill(['is_override' => $routeUrlData->is_override])
        //     ->touch();

        return $routeUrl;
    }
}
