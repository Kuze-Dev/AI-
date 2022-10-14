<?php

declare(strict_types=1);

use App\Events\NotificationRead;
use App\Events\NotificationUnread;
use App\Notifications\Basic\SuccessBasicNotification;
use Database\Factories\AdminFactory;
use Domain\Notification\Exceptions\CantReadNotificationException;
use Domain\Notification\Exceptions\CantUnReadNotificationException;
use Illuminate\Notifications\DatabaseNotification;

use Illuminate\Support\Facades\Event;

use function Pest\Laravel\actingAs;
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
    AdminFactory::new()
        ->createOne()
        ->notify(new SuccessBasicNotification('other notification'));

    $user = AdminFactory::new()
        ->createOne();

    $message = fake()->sentence();

    $notification = new SuccessBasicNotification($message);

    $user->notify($notification);

    // TODO: move to helper
    actingAs($user);

    assertDatabaseCount(DatabaseNotification::class, 2);

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

    $user = AdminFactory::new()
        ->createOne();

    $user->notify(new SuccessBasicNotification(fake()->sentence()));

    // TODO: move to helper
    actingAs($user);

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

    $user = AdminFactory::new()
        ->createOne();

    $user->notify(new SuccessBasicNotification(fake()->sentence()));

    // TODO: move to helper
    actingAs($user);

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
    $userOther = AdminFactory::new()
        ->createOne();

    $userOther->notify(new SuccessBasicNotification(fake()->sentence()));

    $user = AdminFactory::new()
        ->createOne();

    // TODO: move to helper
    actingAs($user);

    assertDatabaseCount(DatabaseNotification::class, 1);
    $notification = DatabaseNotification::first();

    withoutExceptionHandling();

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
