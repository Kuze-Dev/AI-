<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Notification;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Prefix;

#[
    Prefix('notifications'),
    // TODO: auth for guard api
    Middleware(['auth:sanctum'])
]
class NotificationCountController
{
    #[Get('/count')]
    public function __invoke(): JsonResponse
    {

        if ( ! $user = Auth::user()) {
            throw new AuthenticationException();
        }

        $unreadCount = $user->notifications()->whereNull('read_at')->count();

        return response()->json(['count' => $unreadCount]);

    }
}
