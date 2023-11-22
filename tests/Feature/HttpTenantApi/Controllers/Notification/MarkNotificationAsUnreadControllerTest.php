<?php

declare(strict_types=1);

use Domain\Customer\Database\Factories\CustomerFactory;
use Domain\Notification\Events\NotificationUnread;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Event;
use Tests\Fixtures\TestNotification;

use function Pest\Laravel\patchJson;
use function Pest\Laravel\withHeader;
use function PHPUnit\Framework\assertTrue;

// beforeEach(function () {
//     testInTenantContext();
//     $customer = CustomerFactory::new()->createOne();

//     withHeader('Authorization', 'Bearer ' . $customer
//         ->createToken('testing-auth')
//     ->plainTextToken);

//     $this->customer = $customer;

//     return $customer;
// });

// it('mark as un-read', function () {
//     Event::fake();

//     $user = $this->customer;
//     $user->notify(new TestNotification(fake()->sentence()));

//     /** @var DatabaseNotification $notification */
//     $notification = DatabaseNotification::first();

//     $notification->markAsRead();

//     assertTrue($notification->refresh()->read());

//     patchJson('api/notifications/' . $notification->getRouteKey() . '/mark-as-unread')
//         ->assertOk()
//         ->assertValid();

//     Event::assertDispatched(NotificationUnread::class, 1);

//     assertTrue($notification->refresh()->unread());
// });
