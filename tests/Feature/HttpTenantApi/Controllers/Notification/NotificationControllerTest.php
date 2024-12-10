<?php

declare(strict_types=1);

use Domain\Customer\Database\Factories\CustomerFactory;
use Domain\Customer\Models\Customer;
use Domain\Notification\Exceptions\CantReadNotificationException;
use Domain\Notification\Exceptions\CantUnReadNotificationException;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\Fixtures\TestNotification;

use function Pest\Laravel\getJson;
use function Pest\Laravel\patchJson;
use function Pest\Laravel\withHeader;
use function Pest\Laravel\withoutExceptionHandling;

beforeEach(function () {
    testInTenantContext();
    $customer = CustomerFactory::new()->createOne();

    withHeader('Authorization', 'Bearer '.$customer
        ->createToken('testing-auth')
        ->plainTextToken);

    $this->customer = $customer;

    return $customer;
});

it('return list', function () {
    createNotificationForCustomer();

    $message = fake()->sentence();

    $notification = new TestNotification($message);

    $user = $this->customer;
    $user->notify($notification);

    getJson('api/notifications')
        ->assertOk()
        ->assertJson(function (AssertableJson $json) use ($notification, $message) {
            $json
                ->count('data', 1)
                ->where('data.0.attributes.data.message', $message)
                ->where('data.0.attributes.type', $notification->databaseType())
                ->where('data.0.attributes.read_at', null)
                ->etc();
        });
});

it('cant update in not associated', function (string $url, string $exception) {
    $otherUSer = createNotificationForCustomer();

    $notification = $otherUSer->notifications()->first();

    withoutExceptionHandling();

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
    createNotificationForCustomer();

    $notification = new TestNotification(fake()->sentence());

    $user = $this->customer;
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

    $user = $this->customer;

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

function createNotificationForCustomer(): Customer
{
    $customer = CustomerFactory::new()->createOne();

    $customer->notify(new TestNotification(fake()->sentence()));
    $customer->notify(new TestNotification(fake()->sentence()));

    /** @var DatabaseNotification $notification */
    $notification = $customer->notifications->first();

    $notification->markAsRead();

    return $customer;
}
