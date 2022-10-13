<?php

declare(strict_types=1);

use App\Enums\Api\NotificationType;
use Database\Factories\AdminFactory;
use Domain\Notification\Actions\SendNotificationAction;
use Domain\Notification\Exceptions\CantReadNotificationException;
use Domain\Notification\Exceptions\CantUnReadNotificationException;
use Illuminate\Notifications\DatabaseNotification;

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
    app(SendNotificationAction::class)
        ->execute(
            AdminFactory::new()
                ->createOne(),
            'other notification'
        );

    $user = AdminFactory::new()
        ->createOne();

    $message = fake()->sentence();

    app(SendNotificationAction::class)
        ->execute($user, $message);

    // TODO: move to helper
    actingAs($user);

    assertDatabaseCount(DatabaseNotification::class, 2);

    getJson('api/notifications')
        ->assertOk()
        ->assertJson(function (Illuminate\Testing\Fluent\AssertableJson $json) use ($message) {
            $json
                ->count('data', 1)
                ->where('data.0.attributes.message', $message)
                ->where('data.0.attributes.type', NotificationType::INFORMATION->value)
                ->etc();
        });
});

it('mark as read', function () {
    $user = AdminFactory::new()
        ->createOne();

    app(SendNotificationAction::class)
        ->execute($user, fake()->sentence());

    // TODO: move to helper
    actingAs($user);

    assertDatabaseCount(DatabaseNotification::class, 1);
    /** @var DatabaseNotification $notification */
    $notification = DatabaseNotification::first();

    assertTrue($notification->unread());

    patchJson('api/notifications/' . $notification->getRouteKey() . '/mark-as-read')
        ->assertOk()
        ->assertValid();

    assertTrue($notification->refresh()->read());
});

it('mark as un-read', function () {
    $user = AdminFactory::new()
        ->createOne();

    app(SendNotificationAction::class)
        ->execute($user, fake()->sentence());

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

    assertTrue($notification->refresh()->unread());
});

it('cant update in not associated', function (string $url, string $exception) {
    $userOther = AdminFactory::new()
        ->createOne();

    app(SendNotificationAction::class)
        ->execute($userOther, fake()->sentence());

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
