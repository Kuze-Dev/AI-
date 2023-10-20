<?php

declare(strict_types=1);

use Domain\Admin\Database\Factories\AdminFactory;
use Domain\Admin\Models\Admin;
use Domain\Notification\Exceptions\CantReadNotificationException;
use Domain\Notification\Exceptions\CantUnReadNotificationException;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\Fixtures\TestNotification;

use function Pest\Laravel\getJson;
use function Pest\Laravel\patchJson;
use function Pest\Laravel\withoutExceptionHandling;

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

it('cant update in not associated', function (string $url, string $exception) {
    $otherUSer = createNotificationForOtherUser();

    $notification = $otherUSer->notifications()->first();

    withoutExceptionHandling();

    loginAsUser();
    expect(fn () => patchJson('api/notifications/'.$notification->getRouteKey().$url))
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

it('filter', function (string $status, int $dataCount) {
    createNotificationForOtherUser();

    $notification = new TestNotification(fake()->sentence());

    $user = loginAsUser();
    $user->notify($notification);
    $user->notify($notification);

    /** @var DatabaseNotification $notificationModel */
    $notificationModel = $user->notifications()->first();
    $notificationModel->markAsRead();

    getJson('api/notifications?'.http_build_query(['filter[status]' => $status]))
        ->assertOk()
        ->assertJson(function (AssertableJson $json) use ($status, $dataCount, $user, $notification) {
            $json
                ->count('data', $dataCount)
                ->where('data.0.attributes.data', $notification->toArray($user))
                ->whereType(
                    'data.0.attributes.read_at',
                    match ($status) {
                        'unread' => 'null',
                        'read' => 'string',
                        '' => 'string|null'
                    }
                )
                ->etc();
        });
})
    ->with([
        ['unread', 1],
        ['read', 1],
        'all' => ['', 2],
    ]);

it('paginate', function (int $page, int $size) {
    $notification = new TestNotification(fake()->sentence());

    $user = loginAsUser();

    foreach (range(1, 40) as $i) {
        $user->notify($notification);
    }

    getJson('api/notifications?'.http_build_query([
        'page[number]' => $page,
        'page[size]' => $size,
    ]))
        ->assertOk()
        ->assertJson(function (AssertableJson $json) use ($size) {
            $json
                ->count('data', $size)
                ->etc();
        });
})
    ->with([
        'page 1' => 1,
        'page 2' => 2,
    ])
    ->with([
        'size 10' => 10,
        'size 20' => 20,
    ]);

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
