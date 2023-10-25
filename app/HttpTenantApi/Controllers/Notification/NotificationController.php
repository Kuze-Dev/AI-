<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Notification;

use App\HttpApi\Resources\NotificationResource;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Resource;
use TiMacDonald\JsonApi\JsonApiResourceCollection;

#[
    Resource('notifications', apiResource: true, only: ['index']),
    // TODO: auth for guard api
    Middleware(['auth:sanctum'])
]
class NotificationController
{
    public function index(): JsonApiResourceCollection
    {
        if (! $user = Auth::user()) {
            throw new AuthenticationException();
        }

        return NotificationResource::collection(
            QueryBuilder::for(
                $user->notifications()
            )
                ->allowedFilters([
                    AllowedFilter::callback(
                        'status',
                        function (Builder $query, string $value) {
                            match ($value) {
                                'unread' => $query->whereNull('read_at'),
                                'read' => $query->whereNotNull('read_at'),
                                default => '',
                            };
                        }
                    ),
                ])
                ->jsonPaginate()
        );
    }
}
