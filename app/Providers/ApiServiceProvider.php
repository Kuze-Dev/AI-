<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use TiMacDonald\JsonApi\JsonApiResource;

class ApiServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        JsonApiResource::resolveIdUsing(fn (Model $resource, Request $request): string => $resource->getRouteKey());
    }
}
