<?php

declare(strict_types=1);

use Domain\Notification\Events\NotificationRead;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Event;
use Tests\Fixtures\TestNotification;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\patchJson;
use function PHPUnit\Framework\assertTrue;

it('mark as read', function () {
    Event::fake();

    $user = loginAsUser();
    $user->notify(new TestNotification(fake()->sentence()));

    assertDatabaseCount(DatabaseNotification::class, 1);
    /** @var DatabaseNotification $notification */
    $notification = DatabaseNotification::first();

    assertTrue($notification->unread());

    patchJson('api/notifications/' . $notification->getRouteKey() . '/mark-as-read')
        ->assertOk()
        ->assertValid();

    Event::assertDispatched(NotificationRead::class, 1);

    assertTrue($notification->refresh()->read());
});
