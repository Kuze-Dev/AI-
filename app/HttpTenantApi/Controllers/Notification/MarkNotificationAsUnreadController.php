<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Notification;

use Domain\Notification\Actions\MarkAsUnreadNotificationAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Auth;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Patch;
use Spatie\RouteAttributes\Attributes\Prefix;

#[
    Prefix('notifications'),
    // TODO: auth for guard api
    Middleware(['auth:sanctum'])
]
class MarkNotificationAsUnreadController
{
    #[Patch('/{databaseNotification}/mark-as-unread')]
    public function update(DatabaseNotification $databaseNotification): JsonResponse
    {
        app(MarkAsUnreadNotificationAction::class)
            /** @phpstan-ignore argument.type  */
            ->execute(Auth::user(), $databaseNotification);

        return response()
            ->json([
                'message' => 'Successfully unread notification!',
            ]);
    }
}
