<?php

declare(strict_types=1);

namespace Domain\Notification\Actions;

use App\Notifications\Api\GeneralApiNotification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\User;
use Illuminate\Notifications\DatabaseNotification;

class GetAllNotificationForCurrentAccount
{
    /** @phpstan-ignore-next-line  */
    public function execute(User $user): Builder
    {
        return DatabaseNotification::query()
            ->where('type', GeneralApiNotification::class)
            ->where('notifiable_type', $user->getMorphClass())
            ->where('notifiable_id', $user->getKey());
    }
}
