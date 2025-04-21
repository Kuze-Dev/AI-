<?php

declare(strict_types=1);

use Domain\Notification\Events\NotificationUnread;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Event;
use Tests\Fixtures\TestNotification;

use function Pest\Laravel\patchJson;
use function PHPUnit\Framework\assertTrue;

it('mark as un-read', function () {
    Event::fake(NotificationUnread::class);

    $user = loginAsUser();
    $user->notify(new TestNotification(fake()->sentence()));

    /** @var DatabaseNotification $notification */
    $notification = DatabaseNotification::first();

    $notification->markAsRead();

    assertTrue($notification->refresh()->read());

    patchJson('api/notifications/'.$notification->getRouteKey().'/mark-as-unread')
        ->assertOk()
        ->assertValid();

    Event::assertDispatched(NotificationUnread::class, 1);

    assertTrue($notification->refresh()->unread());
});
