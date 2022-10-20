<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Notification;

use App\Http\Controllers\Controller;
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
    Middleware(['auth']),
]
class MarkNotificationAsUnreadController extends Controller
{
    #[Patch('{databaseNotification}/mark-as-unread')]
    public function __invoke(DatabaseNotification $databaseNotification): JsonResponse
    {
        app(MarkAsUnreadNotificationAction::class)
            /** @phpstan-ignore-next-line  */
            ->execute(Auth::user(), $databaseNotification);

        return response()
            ->json([
                'message' => 'Successfully unread notification!',
            ]);
    }
}
