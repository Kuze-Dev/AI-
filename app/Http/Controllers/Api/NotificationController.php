<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resource\NotificationResource;
use Domain\Notification\Actions\GetAllNotificationForCurrentAccount;
use Domain\Notification\Actions\MarkAsReadNotificationAction;
use Domain\Notification\Actions\MarkAsUnReadNotificationAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Auth;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Patch;
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
            app(GetAllNotificationForCurrentAccount::class)
                /** @phpstan-ignore-next-line  */
                ->execute(Auth::user())
                ->paginate()
        );
    }

    #[Patch('{databaseNotification}/mark-as-read')]
    public function markAsRead(DatabaseNotification $databaseNotification): JsonResponse
    {
        app(MarkAsReadNotificationAction::class)
            /** @phpstan-ignore-next-line  */
            ->execute(Auth::user(), $databaseNotification);

        return response()
            ->json([
                'message' => 'Successfully read notification!',
            ]);
    }

    #[Patch('{databaseNotification}/mark-as-unread')]
    public function markAsUnRead(DatabaseNotification $databaseNotification): JsonResponse
    {
        app(MarkAsUnReadNotificationAction::class)
            /** @phpstan-ignore-next-line  */
            ->execute(Auth::user(), $databaseNotification);

        return response()
            ->json([
                'message' => 'Successfully unread notification!',
            ]);
    }
}
