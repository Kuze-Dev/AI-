<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Notification;

use App\Http\Controllers\Controller;
use App\Http\Resource\NotificationResource;
use Illuminate\Support\Facades\Auth;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Prefix;
use TiMacDonald\JsonApi\JsonApiResourceCollection;

#[
    Prefix('api/notifications'),
    // TODO: auth for guard api
    Middleware(['api', 'auth']),
]
class NotificationController extends Controller
{
    #[Get('/')]
    public function index(): JsonApiResourceCollection
    {
        return NotificationResource::collection(
            QueryBuilder::for(
                Auth::user()->notifications()
            )
                ->paginate()
        );
    }
}
