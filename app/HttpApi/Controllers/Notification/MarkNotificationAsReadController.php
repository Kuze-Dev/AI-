<?php

declare(strict_types=1);

namespace App\HttpApi\Controllers\Notification;

use Domain\Notification\Actions\MarkAsReadNotificationAction;
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
class MarkNotificationAsReadController
{
    #[Patch('{databaseNotification}/mark-as-read')]
    public function __invoke(DatabaseNotification $databaseNotification): JsonResponse
    {
        app(MarkAsReadNotificationAction::class)
            /** @phpstan-ignore argument.type  */
            ->execute(Auth::user(), $databaseNotification);

        return response()
            ->json([
                'message' => 'Successfully read notification!',
            ]);
    }
}
