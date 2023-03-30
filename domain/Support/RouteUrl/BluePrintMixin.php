<?php

declare(strict_types=1);

namespace Domain\Support\RouteUrl;

use Closure;

/**
 * @mixin \Illuminate\Database\Schema\Blueprint
 */
class BluePrintMixin
{
    public function routeUrl(): Closure
    {
        /**
         * Columns fields for Route Url Manage by RouteUrl Domain
         */
        return function (): void {
            $this->string('route_url')->unique()->comment('Manage by RouteUrl Domain');
            $this->boolean('route_url_is_override')->comment('Manage by RouteUrl Domain');
        };
    }
}
