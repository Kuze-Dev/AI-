<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Notification;

use App\Http\Controllers\Controller;
use App\Http\Resource\NotificationResource;
use App\Notifications\Basic\SuccessBasicNotification;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Auth;
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
    public function __invoke(): JsonApiResourceCollection
    {
        /** @var \Domain\Admin\Models\Admin $user */
        $user = Auth::user();

        return NotificationResource::collection(
            DatabaseNotification::query()
                ->whereIn('type', [
                    SuccessBasicNotification::databaseType()
                ])
//                ->whereMorphedTo()
                ->where('notifiable_type', $user->getMorphClass())
                ->where('notifiable_id', $user->getKey())
                ->paginate()
        );
    }
}
