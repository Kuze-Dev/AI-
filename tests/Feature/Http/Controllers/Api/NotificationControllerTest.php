<?php

declare(strict_types=1);

use Database\Factories\AdminFactory;
use Domain\Admin\Models\Admin;
use Domain\Notification\Events\NotificationRead;
use Domain\Notification\Events\NotificationUnread;
use Domain\Notification\Exceptions\CantReadNotificationException;
use Domain\Notification\Exceptions\CantUnReadNotificationException;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Event;
use Tests\Fixtures\TestNotification;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\getJson;
use function Pest\Laravel\patchJson;
use function Pest\Laravel\withoutExceptionHandling;
use function PHPUnit\Framework\assertTrue;

it('require login', function () {
    getJson('api/notifications')
        ->assertUnauthorized();
});

it('return list', function () {
    createNotificationForOtherUser();

    $message = fake()->sentence();

    $notification = new TestNotification($message);

    $user = loginAsUser();
    $user->notify($notification);

    getJson('api/notifications')
        ->assertOk()
        ->assertJson(function (Illuminate\Testing\Fluent\AssertableJson $json) use ($notification, $message) {
            $json
                ->count('data', 1)
                ->where('data.0.attributes.data.message', $message)
                ->where('data.0.attributes.type', $notification->databaseType())
                ->where('data.0.attributes.read_at', null)
                ->etc();
        });
});

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

it('mark as un-read', function () {
    Event::fake();

    $user = loginAsUser();
    $user->notify(new TestNotification(fake()->sentence()));

    assertDatabaseCount(DatabaseNotification::class, 1);
    /** @var DatabaseNotification $notification */
    $notification = DatabaseNotification::first();

    $notification->markAsRead();

    assertTrue($notification->refresh()->read());

    patchJson('api/notifications/' . $notification->getRouteKey() . '/mark-as-unread')
        ->assertOk()
        ->assertValid();

    Event::assertDispatched(NotificationUnread::class, 1);

    assertTrue($notification->refresh()->unread());
});

it('cant update in not associated', function (string $url, string $exception) {
    $otherUSer = createNotificationForOtherUser();

    $notification = $otherUSer->notifications()->first();

    withoutExceptionHandling();

    loginAsUser();
    expect(fn () => patchJson('api/notifications/' . $notification->getRouteKey() . $url))
        ->toThrow($exception);
})
    ->with([
        [
            '/mark-as-read',
            CantReadNotificationException::class,
        ],
        [
            '/mark-as-unread',
            CantUnReadNotificationException::class,
        ],
    ]);

it('filter read only', function () {
    createNotificationForOtherUser();

    $message = fake()->sentence();
    $notification = new TestNotification($message);

    $user = loginAsUser();
    $user->notify($notification);
    $user->notify($notification);

    /** @var DatabaseNotification $notificationModel */
    $notificationModel = $user->notifications()->first();
    $notificationModel->markAsRead();

    $this->markTestIncomplete('working in progress');

    getJson('api/notifications?'.http_build_query(['filter[]']))
        ->assertOk()
        ->assertJson(function (Illuminate\Testing\Fluent\AssertableJson $json) use ($notification, $message) {
            $json
                ->count('data', 1)
                ->where('data.0.attributes.data.message', $message)
                ->where('data.0.attributes.type', $notification->databaseType())
                ->where('data.0.attributes.read_at', null)
                ->etc();
        });
});

function createNotificationForOtherUser(): Admin
{
    $userOther = AdminFactory::new()
        ->createOne();

    $userOther->notify(new TestNotification(fake()->sentence()));
    $userOther->notify(new TestNotification(fake()->sentence()));

    /** @var DatabaseNotification $notification */
    $notification = $userOther->notifications->first();

    $notification->markAsRead();

    return $userOther;
}
