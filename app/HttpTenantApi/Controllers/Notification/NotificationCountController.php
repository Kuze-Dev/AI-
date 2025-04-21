<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Notification;

use Domain\Customer\Models\Customer;
use Illuminate\Container\Attributes\CurrentUser;
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
    public function __invoke(#[CurrentUser('sanctum')] Customer $user): JsonResponse
    {
        $unreadCount = $user->notifications()->whereNull('read_at')->count();

        return response()->json(['count' => $unreadCount]);

    }
}
